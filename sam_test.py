import rasterio
from segment_anything import SamAutomaticMaskGenerator, sam_model_registry
import cv2
import numpy as np

sam = sam_model_registry["vit_b"](
    checkpoint="checkpoint/sam_vit_b_01ec64.pth"
)

print("SAM Loaded Successfully")
