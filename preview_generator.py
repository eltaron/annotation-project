"""
Geo Annotate — GeoTIFF to PNG preview generator.

Usage:
    python preview_generator.py <input.tif> <output.png>

Reads a GeoTIFF, normalises the first 3 bands (R, G, B) using
percentile stretching, and writes an 8-bit PNG suitable for
browser display.
"""

import sys
import numpy as np
from PIL import Image

try:
    import rasterio
except ImportError:
    print("ERROR: rasterio not installed")
    sys.exit(1)


def _normalise(band: np.ndarray) -> np.ndarray:
    """Percentile-based contrast stretch (2-98%), returns uint8."""
    band = band.astype(np.float32)
    mask = band > 0
    if not np.any(mask):
        mask = np.ones_like(band, dtype=bool)
    p2, p98 = np.percentile(band[mask], [2, 98])
    band = np.clip((band - p2) / (p98 - p2 + 1e-10) * 255, 0, 255)
    return band.astype(np.uint8)


def convert(tif_path: str, png_path: str) -> None:
    with rasterio.open(tif_path) as src:
        count = src.count

        if count >= 3:
            r = _normalise(src.read(1))
            g = _normalise(src.read(2))
            b = _normalise(src.read(3))
        elif count == 2:
            r = _normalise(src.read(1))
            g = _normalise(src.read(2))
            b = np.zeros_like(r)
        else:
            gray = _normalise(src.read(1))
            r = g = b = gray

        rgb = np.stack([r, g, b], axis=-1)
        img = Image.fromarray(rgb)

        if src.width > 2048 or src.height > 2048:
            ratio = min(2048 / src.width, 2048 / src.height)
            img = img.resize(
                (int(src.width * ratio), int(src.height * ratio)),
                Image.LANCZOS,
            )

        img.save(png_path, "PNG", optimize=True)


if __name__ == "__main__":
    if len(sys.argv) != 3:
        print(f"Usage: python {sys.argv[0]} <input.tif> <output.png>")
        sys.exit(1)
    convert(sys.argv[1], sys.argv[2])
