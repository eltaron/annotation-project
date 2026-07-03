import io
import rasterio
import cv2
import numpy as np
from PIL import Image
import torch
from threading import Lock
from segment_anything import sam_model_registry, SamPredictor
from rasterio.features import shapes
import json


class AdvancedSAMSegmenter:
    def __init__(self, checkpoint_path="checkpoint/sam_vit_b_01ec64.pth", model_type="vit_b"):

        self.device = "cuda" if torch.cuda.is_available() else "cpu"

        self.sam = sam_model_registry[model_type](checkpoint=checkpoint_path)
        self.sam.to(self.device)
        self.predictor = SamPredictor(self.sam)

        self.lock = Lock()

        # Image
        self.rgb_image = None
        self.original_h = 0
        self.original_w = 0
        self.scaled_h = 0
        self.scaled_w = 0
        self.scale_factor = 1.0
        self.max_dimension = 2048

        # Geo
        self.crs = None
        self.transform = None
        self.pixel_area_meters = 1.0

        # Masks
        self.label_map = None
        self.combined_mask = None
        self.class_mask = {}
        self.class_colors = {}

        # History
        self.undo_stack = []
        self.max_undo_history = 20

        # Stats
        self.class_stats = {}

    # ---------------------------
    # LOAD IMAGE
    # ---------------------------
    def load_image(self, file_bytes):

        with self.lock:
            with rasterio.MemoryFile(file_bytes) as memfile:
                with memfile.open() as src:

                    if src.count < 3:
                        raise ValueError("Image must have at least 3 bands (RGB)")

                    self.crs = src.crs.to_string() if src.crs else "EPSG:4326"
                    self.transform = src.transform

                    res_x, res_y = src.res
                    self.pixel_area_meters = abs(res_x * res_y)

                    self.original_h = src.height
                    self.original_w = src.width

                    r = src.read(1).astype(float)
                    g = src.read(2).astype(float)
                    b = src.read(3).astype(float)

                    rgb = np.dstack((r, g, b))
                    p2, p98 = np.percentile(rgb, (2, 98))
                    rgb = np.clip(rgb, p2, p98)

                    if p98 - p2 == 0:
                        self.rgb_image = np.zeros_like(rgb, dtype=np.uint8)
                    else:
                        self.rgb_image = ((rgb - p2) / (p98 - p2) * 255).astype(np.uint8)

            # Resize if needed
            self.scaled_h, self.scaled_w = self.original_h, self.original_w
            self.scale_factor = 1.0

            if max(self.original_h, self.original_w) > self.max_dimension:
                self.scale_factor = self.max_dimension / max(self.original_h, self.original_w)

                self.scaled_w = int(self.original_w * self.scale_factor)
                self.scaled_h = int(self.original_h * self.scale_factor)

                self.rgb_image = cv2.resize(
                    self.rgb_image,
                    (self.scaled_w, self.scaled_h),
                    interpolation=cv2.INTER_LINEAR
                )

            # Init maps
            self.label_map = np.zeros((self.scaled_h, self.scaled_w), dtype=np.uint16)
            self.combined_mask = np.zeros((self.scaled_h, self.scaled_w), dtype=np.uint8)
            self.class_mask = {}
            self.class_colors = {}
            self.class_stats = {}
            self.undo_stack = []

            self.predictor.set_image(self.rgb_image)

            return self.rgb_image

    # ---------------------------
    # GEO CONVERSION
    # ---------------------------
    def _convert_pixel_to_geo(self, x, y):

        if self.transform is None:
            return None, None

        orig_x = x / self.scale_factor
        orig_y = y / self.scale_factor

        return self.transform * (orig_x, orig_y)

    # ---------------------------
    # UNDO
    # ---------------------------
    def _store_undo(self):

        if len(self.undo_stack) >= self.max_undo_history:
            self.undo_stack.pop(0)

        self.undo_stack.append({
            "label_map": self.label_map.copy(),
            "combined_mask": self.combined_mask.copy(),
            "class_mask": {k: v.copy() for k, v in self.class_mask.items()},
            "class_colors": self.class_colors.copy(),
            "class_stats": json.loads(json.dumps(self.class_stats))
        })

    def undo(self):
        with self.lock:

            if not self.undo_stack:
                return None

            state = self.undo_stack.pop()

            self.label_map = state["label_map"]
            self.combined_mask = state["combined_mask"]
            self.class_mask = state["class_mask"]
            self.class_colors = state["class_colors"]
            self.class_stats = state["class_stats"]

            return self._rebuild_overlay()

    # ---------------------------
    # OVERLAY
    # ---------------------------
    def _rebuild_overlay(self):

        overlay = np.zeros_like(self.rgb_image)

        for class_id, mask in self.class_mask.items():
            if class_id in self.class_colors:
                hex_color = self.class_colors[class_id].lstrip("#")
                rgb = tuple(int(hex_color[i:i+2], 16) for i in (0, 2, 4))
                overlay[mask] = rgb

        result = cv2.addWeighted(self.rgb_image, 0.6, overlay, 0.4, 0)

        img = Image.fromarray(result)
        buf = io.BytesIO()
        img.save(buf, format="PNG")

        return buf.getvalue()

    # ---------------------------
    # SEGMENTATION CLICK
    # ---------------------------
    def segment_with_click(self, click_x, click_y, click_type, class_id, class_color_hex):

        with self.lock:

            if self.rgb_image is None:
                raise ValueError("Load image first")

            self._store_undo()
            self.class_colors[class_id] = class_color_hex

            x = int(click_x)
            y = int(click_y)

            masks, scores, _ = self.predictor.predict(
                point_coords=np.array([[x, y]]),
                point_labels=np.array([click_type]),
                multimask_output=True
            )

            new_mask = masks[np.argmax(scores)]

            if class_id not in self.class_mask:
                self.class_mask[class_id] = np.zeros((self.scaled_h, self.scaled_w), dtype=bool)
                self.class_stats[class_id] = {
                    "total_pixels": 0,
                    "instances": 0,
                    "area_m2": 0.0,
                    "geospatial_metadata": {}
                }

            # ---------------- Positive Click ----------------
            if click_type == 1:
                self.class_mask[class_id] |= new_mask
                self.label_map[new_mask] = class_id
                self.combined_mask[new_mask] = 1
                self.class_stats[class_id]["instances"] += 1

            # ---------------- Negative Click ----------------
            else:
                affected = np.unique(self.label_map[new_mask])

                for cid in affected:
                    if cid in self.class_mask:
                        self.class_mask[cid][new_mask] = False

                self.label_map[new_mask] = 0
                self.combined_mask[new_mask] = 0

            # ---------------- Stats ----------------
            total_pixels = int(np.sum(self.class_mask[class_id]))

            area = total_pixels * self.pixel_area_meters / (self.scale_factor ** 2)

            geo_meta = {}

            if total_pixels > 0:
                ys, xs = np.where(self.class_mask[class_id])

                min_x, max_x = xs.min(), xs.max()
                min_y, max_y = ys.min(), ys.max()

                gx1, gy1 = self._convert_pixel_to_geo(min_x, min_y)
                gx2, gy2 = self._convert_pixel_to_geo(max_x, max_y)

                geo_meta = {
                    "crs": self.crs,
                    "bbox": {
                        "minx": float(gx1),
                        "miny": float(gy2),
                        "maxx": float(gx2),
                        "maxy": float(gy1)
                    }
                }

            self.class_stats[class_id].update({
                "total_pixels": total_pixels,
                "area_m2": float(area),
                "geospatial_metadata": geo_meta
            })

            return {
                "image_bytes": self._rebuild_overlay(),
                "class_statistics": self.class_stats[class_id],
                "all_stats": self.class_stats
            }

    # ---------------------------
    # GEOJSON EXPORT 
    # ---------------------------
    def export_geojson(self, class_id):

        if class_id not in self.class_mask:
            return None

        mask = self.class_mask[class_id].astype(np.uint8)

        features = []

        for geom, val in shapes(mask, mask=mask, transform=self.transform):
            if val == 1:
                features.append({
                    "type": "Feature",
                    "geometry": geom,
                    "properties": {
                        "class_id": int(class_id)
                    }
                })

        return {
            "type": "FeatureCollection",
            "features": features
        }