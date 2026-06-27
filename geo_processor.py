import rasterio
import numpy as np
import io
from PIL import Image
import matplotlib.pyplot as plt

def process_geotiff(file_bytes):
    """
    Processes a 4-band GeoTIFF, generates an exact NDVI Heatmap with masked non-vegetation,
    and calculates statistical distribution for the Decision Support System (DSS).
    """
    with rasterio.MemoryFile(file_bytes) as memfile:
        with memfile.open() as src:
            # 1. Read Bands (1: Red, 2: Green, 3: Blue, 4: NIR)
            r = src.read(1).astype(float)
            g = src.read(2)
            b = src.read(3)
            nir = src.read(4).astype(float)
            
            # Get pixel resolution to calculate actual area in square meters
            res_x, res_y = src.res
            pixel_area = res_x * res_y
            
            # 2. Generate Standard RGB View
            rgb_image = np.dstack((r, g, b))
            rgb_image = ((rgb_image - rgb_image.min()) / (rgb_image.max() - rgb_image.min()) * 255).astype(np.uint8)
            
            pil_rgb = Image.fromarray(rgb_image)
            rgb_io = io.BytesIO()
            pil_rgb.save(rgb_io, format='PNG')
            rgb_png_bytes = rgb_io.getvalue()
            
            # 3. Calculate NDVI
            with np.errstate(divide='ignore', invalid='ignore'):
                denom = nir + r
                ndvi = np.where(denom == 0, 0, (nir - r) / denom)
                ndvi = np.nan_to_num(ndvi, nan=0.0)
            
            # 4. Calculate Crop Health Statistics (DSS Data)
            total_pixels = ndvi.size
            
            excellent_mask = ndvi > 0.5
            good_mask = (ndvi > 0.3) & (ndvi <= 0.5)
            stressed_mask = (ndvi > 0.1) & (ndvi <= 0.3)
            poor_mask = ndvi <= 0.1
            
            counts = {
                "Excellent": np.sum(excellent_mask),
                "Good": np.sum(good_mask),
                "Stressed": np.sum(stressed_mask),
                "Poor": np.sum(poor_mask)
            }
            
            stats = {}
            for class_name, count in counts.items():
                percentage = (count / total_pixels) * 100
                area_m2 = count * pixel_area
                stats[class_name] = {
                    "percentage": round(percentage, 1),
                    "area_m2": round(area_m2, 2)
                }
            
            # 5. Advanced Color Mapping with Non-Vegetation Masking
            # Create an empty RGB array for the final heatmap
            height, width = ndvi.shape
            ndvi_colored = np.zeros((height, width, 3), dtype=np.uint8)
            
            # Step A: For actual vegetation (NDVI > 0.1), apply the Yellow-to-Green colormap
            veg_mask = ndvi > 0.1
            if np.any(veg_mask):
                # Normalize ONLY the vegetation pixels between 0.1 and the maximum NDVI value found
                max_val = max(ndvi.max(), 0.11)
                ndvi_norm = (ndvi - 0.1) / (max_val - 0.1)
                
                cmap = plt.get_cmap('YlGn') # Dynamic Yellow to Green for crops
                colors_mapped = (cmap(ndvi_norm)[:, :, :3] * 255).astype(np.uint8)
                
                # Apply colors only where vegetation exists
                ndvi_colored[veg_mask] = colors_mapped[veg_mask]
            
            # Step B: For non-vegetation / bare soil (NDVI <= 0.1), force it to be RED/BROWN (No green at all)
            # Standard HEX for deep red/brown in RGB: [180, 40, 40]
            ndvi_colored[poor_mask] = [180, 40, 40]
            
            # Convert to PNG bytes
            pil_ndvi = Image.fromarray(ndvi_colored)
            ndvi_io = io.BytesIO()
            pil_ndvi.save(ndvi_io, format='PNG')
            ndvi_png_bytes = ndvi_io.getvalue()
            
            return rgb_png_bytes, ndvi_png_bytes, stats
        