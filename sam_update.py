import io
import os
import json
import zlib
import pickle
import base64
import logging
import cv2
import csv
import numpy as np
from PIL import Image
import torch
import subprocess
import shutil
from pathlib import Path
from threading import Lock
from datetime import datetime
import rasterio
from rasterio.enums import Resampling
from rasterio.features import shapes
from segment_anything import sam_model_registry, SamPredictor
from xml.etree import ElementTree as ET
from xml.etree.ElementTree import Element, SubElement

# Import your custom modules safely
try:
    from classifier import EuroSATResNetClassifier
except ImportError:
    EuroSATResNetClassifier = None

# Set default temporary and project directories
TEMP_DIR = os.getenv("TEMP_DIR", "/tmp")
COG_DIR = os.getenv("COG_DIR", os.path.join(TEMP_DIR, "cog_outputs"))
PROJECTS_DIR = "data_projects"
Path(COG_DIR).mkdir(parents=True, exist_ok=True)
Path(PROJECTS_DIR).mkdir(parents=True, exist_ok=True)

logger = logging.getLogger(__name__)

# =========================================================================
# State Compression Helper for Memory Protection
# =========================================================================
class CompressedState:
    """Helper to compress and store matrix states to protect Server RAM/Session bloating."""
    def __init__(self, data):
        self.compressed_data = zlib.compress(pickle.dumps(data))

    def decompress(self):
        return pickle.loads(zlib.decompress(self.compressed_data))


# =========================================================================
# Advanced SAM Geospatial Segmenter Class with Classifier Integration
# =========================================================================
class AdvancedSAMSegmenter:
    def __init__(self, checkpoint_path="checkpoint/sam_vit_b_01ec64.pth", model_type="vit_b", classifier_weights="satellite_model.pth"):
        self.device = "cuda" if torch.cuda.is_available() else "cpu"
        print(f"[SAM INFO] Running on device: {self.device}")

        # Check for SAM weight checkpoint
        os.makedirs(os.path.dirname(checkpoint_path), exist_ok=True)
        if not os.path.exists(checkpoint_path):
            raise FileNotFoundError(f"SAM checkpoint not found at {checkpoint_path}.")

        self.sam = sam_model_registry[model_type](checkpoint=checkpoint_path)
        self.sam.to(self.device)
        self.predictor = SamPredictor(self.sam)

        # Initialize EuroSAT ResNet Classifier
        if EuroSATResNetClassifier:
            self.classifier = EuroSATResNetClassifier(weights_path=checkpoint_path.replace("sam_vit_b_01ec64.pth", "classifier_weights.pth"))
        else:
            self.classifier = None
            print("[CLASSIFIER WARNING] EuroSATResNetClassifier module not found.")

        self.lock = Lock()
        self._cleanup_files = set()

        # Image dimensions and scaling properties
        self.rgb_image = None
        self.original_h = 0
        self.original_w = 0
        self.scaled_h = 0
        self.scaled_w = 0
        self.scale_factor = 1.0
        self.max_dimension = 2048

        # Geospatial Metadata
        self.crs = None
        self.transform = None
        self.pixel_area_meters = 1.0
        self.image_path = None

        # Masks and Label Maps
        self.label_map = None
        self.combined_mask = None
        self.class_mask = {}
        
        # EuroSAT Target Classes Map
        self.class_names = [
            "Annual Crop", "Forest", "Herbaceous Vegetation", "Highway",
            "Industrial", "Pasture", "Permanent Crop", "Residential",
            "River", "Sea Lake"
        ]
        
        # Color mapping generation dynamically for EuroSAT classes
        self.class_colors = {}
        np.random.seed(42)  # Consistent color palette
        for idx, name in enumerate(self.class_names):
            color = np.random.randint(50, 255, size=3)
            self.class_colors[idx + 1] = f"#{color[0]:02x}{color[1]:02x}{color[2]:02x}"

        # History and Stats Tracking
        self.undo_stack = []
        self.max_undo_history = 20
        self.class_stats = {}

    # ---------------------------------------------------------------------
    # GDAL Cloud-Optimized GeoTIFF (COG) Pipeline
    # ---------------------------------------------------------------------
    def _run_gdal_command(self, command):
        try:
            result = subprocess.run(command, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
            if result.returncode != 0:
                raise RuntimeError(f"GDAL sub-process failed: {result.stderr}")
        except subprocess.CalledProcessError as e:
            raise RuntimeError(f"GDAL execution command failed: {e.stderr}")

    def _process_tiff_with_cog(self, tiff_path):
        """Converts standard GeoTIFF into a web-optimized COG with EPSG:3857 projection."""
        try:
            base_name = Path(tiff_path).stem
            step1_output = os.path.join(TEMP_DIR, f'step1_reproj_{base_name}.tif')
            final_output = os.path.join(COG_DIR, f'{base_name}_cog.tif')

            for path in [step1_output, final_output]:
                if os.path.exists(path): os.remove(path)

            self._run_gdal_command(['gdalwarp', '-t_srs', 'EPSG:3857', '-co', 'TILED=YES', '-co', 'COMPRESS=LZW', tiff_path, step1_output])
            self._run_gdal_command(['gdaladdo', '-r', 'average', step1_output, '2', '4', '8', '16', '32', '64'])
            self._run_gdal_command(['gdal_translate', step1_output, final_output, '-co', 'TILED=YES', '-co', 'COMPRESS=LZW', '-co', 'COPY_SRC_OVERVIEWS=YES', '-co', 'BLOCKXSIZE=256', '-co', 'BLOCKYSIZE=256'])

            if os.path.exists(step1_output): os.remove(step1_output)
            return final_output
        except Exception as e:
            logger.exception(f"COG compilation pipeline failed: {e}")
            return tiff_path

    # ---------------------------------------------------------------------
    # Robust Image Loader Component
    # ---------------------------------------------------------------------
    def load_image(self, file_source):
        """Accepts image paths or stream bytes, automatically triggering COG for TIFF."""
        with self.lock:
            if isinstance(file_source, str):
                self.image_path = file_source
                if file_source.lower().endswith(('.tif', '.tiff')) and 'cog_outputs' not in file_source:
                    print("[SAM INFO] Processing TIFF with COG pipeline...")
                    file_source = self._process_tiff_with_cog(file_source)
                    self.image_path = file_source

            with rasterio.open(file_source) as src:
                if src.count < 3:
                    raise ValueError("Target image dataset must possess at least 3 active bands (RGB).")

                self.crs = src.crs.to_string() if src.crs else "EPSG:3857"
                self.transform = src.transform
                self.original_h = src.height
                self.original_w = src.width
                self.pixel_area_meters = abs(src.res[0] * src.res[1])

                r = src.read(1).astype(float)
                g = src.read(2).astype(float)
                b = src.read(3).astype(float)

                rgb = np.dstack((r, g, b))
                p2, p98 = np.percentile(rgb, (2, 98))
                if p98 - p2 == 0:
                    self.rgb_image = np.zeros_like(rgb, dtype=np.uint8)
                else:
                    self.rgb_image = np.clip(((rgb - p2) / (p98 - p2) * 255), 0, 255).astype(np.uint8)

            self.scaled_h, self.scaled_w = self.original_h, self.original_w
            self.scale_factor = 1.0

            if max(self.original_h, self.original_w) > self.max_dimension:
                self.scale_factor = self.max_dimension / max(self.original_h, self.original_w)
                self.scaled_w = int(self.original_w * self.scale_factor)
                self.scaled_h = int(self.original_h * self.scale_factor)
                self.rgb_image = cv2.resize(self.rgb_image, (self.scaled_w, self.scaled_h), interpolation=cv2.INTER_LINEAR)

            self.label_map = np.zeros((self.scaled_h, self.scaled_w), dtype=np.uint16)
            self.combined_mask = np.zeros((self.scaled_h, self.scaled_w), dtype=np.uint8)
            self.class_mask.clear()
            self.class_stats.clear()
            self.undo_stack.clear()

            self.predictor.set_image(self.rgb_image)
            return self.rgb_image

    def _convert_pixel_to_geo(self, x, y):
        if self.transform is None: return None, None
        orig_x = x / self.scale_factor
        orig_y = y / self.scale_factor
        return self.transform * (orig_x, orig_y)

    # ---------------------------------------------------------------------
    # Compressed Undo Stack System Architecture
    # ---------------------------------------------------------------------
    def _store_undo(self):
        if len(self.undo_stack) >= self.max_undo_history:
            self.undo_stack.pop(0)

        state = {
            "label_map": self.label_map.copy(),
            "combined_mask": self.combined_mask.copy(),
            "class_mask": {k: v.copy() for k, v in self.class_mask.items()},
            "class_colors": self.class_colors.copy(),
            "class_stats": json.loads(json.dumps(self.class_stats))
        }
        self.undo_stack.append(CompressedState(state))

    def undo(self):
        with self.lock:
            if not self.undo_stack: return None
            
            state = self.undo_stack.pop().decompress()
            self.label_map = state["label_map"]
            self.combined_mask = state["combined_mask"]
            self.class_mask = state["class_mask"]
            self.class_colors = state["class_colors"]
            self.class_stats = state["class_stats"]

            return self._rebuild_overlay()

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

    # ---------------------------------------------------------------------
    # Interactive Click with Automated Patch Extraction & Classification
    # ---------------------------------------------------------------------
    def segment_with_click(self, click_x, click_y, click_type, class_id, class_color_hex=None):
        with self.lock:
            if self.rgb_image is None: raise ValueError("Initialize and load target image instance first.")

            self._store_undo()
            
            if class_color_hex:
                self.class_colors[class_id] = class_color_hex

            x = int(click_x * self.scale_factor)
            y = int(click_y * self.scale_factor)

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
                    "geospatial_metadata": {},
                    "classification_results": {"predicted_label": "Unknown", "confidence": 0.0}
                }

            if click_type == 1:  # Positive Click
                self.class_mask[class_id] |= new_mask
                self.label_map[new_mask] = class_id
                self.combined_mask[new_mask] = 1
                self.class_stats[class_id]["instances"] += 1
                
                # --- Automated ResNet Classification Trigger ---
                if self.classifier:
                    ys, xs = np.where(new_mask)
                    if len(xs) > 0 and len(ys) > 0:
                        # Crop image patch bounding box around the SAM mask segment
                        min_x, max_x = xs.min(), xs.max()
                        min_y, max_y = ys.min(), ys.max()
                        cropped_patch = self.rgb_image[min_y:max_y+1, min_x:max_x+1]
                        
                        # Call EuroSAT model inference
                        label, confidence = self.classifier.predict_patch(cropped_patch)
                        self.class_stats[class_id]["classification_results"] = {
                            "predicted_label": label,
                            "confidence": confidence
                        }
            else:  # Negative Click
                affected = np.unique(self.label_map[new_mask])
                for cid in affected:
                    if cid in self.class_mask: self.class_mask[cid][new_mask] = False
                self.label_map[new_mask] = 0
                self.combined_mask[new_mask] = 0

            # Compute Surface Calculations
            total_pixels = int(np.sum(self.class_mask[class_id]))
            area = total_pixels * self.pixel_area_meters / (self.scale_factor ** 2)

            geo_meta = {}
            if total_pixels > 0:
                ys, xs = np.where(self.class_mask[class_id])
                gx1, gy1 = self._convert_pixel_to_geo(xs.min(), ys.min())
                gx2, gy2 = self._convert_pixel_to_geo(xs.max(), ys.max())
                geo_meta = {
                    "crs": self.crs,
                    "bbox": {"minx": float(gx1), "miny": float(gy2), "maxx": float(gx2), "maxy": float(gy1)}
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

    # ---------------------------------------------------------------------
    # Geospatial Vector Mapping Exporter (GeoJSON Format)
    # ---------------------------------------------------------------------
    def export_geojson(self, class_id):
        if class_id not in self.class_mask: return None
        mask = self.class_mask[class_id].astype(np.uint8)
        features = []
        
        class_name = self.class_stats.get(class_id, {}).get("classification_results", {}).get("predicted_label", "Unknown")
        
        for geom, val in shapes(mask, mask=mask, transform=self.transform):
            if val == 1:
                features.append({
                    "type": "Feature",
                    "geometry": geom,
                    "properties": {"class_id": int(class_id), "class_name": class_name}
                })
        return {"type": "FeatureCollection", "features": features}

    # ---------------------------------------------------------------------
    # Multi-Format Production Exporter Subsystem
    # ---------------------------------------------------------------------
    def export_all_formats(self, output_dir, project_id="0"):
        try:
            os.makedirs(output_dir, exist_ok=True)
            base_name = Path(self.image_path).stem if self.image_path else "satellite_export"
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            exported_files = {}

            if self.combined_mask is not None:
                binary_path = os.path.join(output_dir, f"{base_name}_binary_{project_id}_{timestamp}.png")
                cv2.imwrite(binary_path, (self.combined_mask * 255).astype(np.uint8))
                exported_files["binary_mask"] = binary_path

                overlay_red_path = os.path.join(output_dir, f"{base_name}_overlay_red_{project_id}_{timestamp}.png")
                overlay_red = np.zeros_like(self.rgb_image)
                overlay_red[:, :, 0] = 255
                red_blend = cv2.addWeighted(self.rgb_image, 1.0, overlay_red, 0.4, 0)
                indices = (self.combined_mask == 0)
                red_blend[indices] = self.rgb_image[indices]
                cv2.imwrite(overlay_red_path, cv2.cvtColor(red_blend, cv2.COLOR_RGB2BGR))
                exported_files["red_overlay"] = overlay_red_path

            csv_path = os.path.join(output_dir, f"{base_name}_report_{timestamp}.csv")
            with open(csv_path, 'w', newline='') as csvfile:
                writer = csv.writer(csvfile)
                writer.writerow(['Class ID', 'Predicted Label', 'Total Pixels', 'Area (m2)'])
                for cid, stats in self.class_stats.items():
                    name = stats.get("classification_results", {}).get("predicted_label", "Unknown")
                    writer.writerow([cid, name, stats.get("total_pixels", 0), stats.get("area_m2", 0.0)])
            exported_files["csv_report"] = csv_path

            # Export Pascal VOC XML format
            annotation = Element('annotation')
            SubElement(annotation, 'filename').text = f"{base_name}.png"
            size = SubElement(annotation, 'size')
            SubElement(size, 'width').text = str(self.scaled_w)
            SubElement(size, 'height').text = str(self.scaled_h)
            SubElement(size, 'depth').text = "3"

            for cid in np.unique(self.label_map):
                if cid == 0: continue
                obj = SubElement(annotation, 'object')
                name = self.class_stats.get(int(cid), {}).get("classification_results", {}).get("predicted_label", "Unknown")
                SubElement(obj, 'name').text = name
                
                binary_mask = (self.label_map == cid).astype(np.uint8)
                contours, _ = cv2.findContours(binary_mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
                for contour in contours:
                    x, y, w, h = cv2.boundingRect(contour)
                    bndbox = SubElement(obj, 'bndbox')
                    SubElement(bndbox, 'xmin').text = str(x)
                    SubElement(bndbox, 'ymin').text = str(y)
                    SubElement(bndbox, 'xmax').text = str(x + w)
                    SubElement(bndbox, 'ymax').text = str(y + h)

            xml_path = os.path.join(output_dir, f"{base_name}_{timestamp}.xml")
            ET.ElementTree(annotation).write(xml_path, encoding='utf-8', xml_declaration=True)
            exported_files["pascal_voc_xml"] = xml_path

            return exported_files
        except Exception as e:
            logger.error(f"Comprehensive export system pipeline encountered error: {str(e)}")
            return {}

    # ---------------------------------------------------------------------
    # Explicit Garbage Collection & VRAM Optimization Release Routine
    # ---------------------------------------------------------------------
    def cleanup_resources(self):
        with self.lock:
            if hasattr(self, 'predictor'): del self.predictor
            if hasattr(self, 'sam'): del self.sam
            if torch.cuda.is_available():
                torch.cuda.empty_cache()
            import gc
            gc.collect()
            print("[SAM INFO] Memory resources cleaned up successfully.")