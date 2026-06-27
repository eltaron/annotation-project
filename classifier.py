import torch
import torchvision.models as models
import torchvision.transforms as transforms
from PIL import Image
import numpy as np

class EuroSATResNetClassifier:
    def __init__(self, weights_path="classifier_weights.pth"):
        """
        Initializes the EuroSAT ResNet-50 classifier.
        :param weights_path: Path to the pre-trained weights file (classifier_weights.pth).
        """
        self.device = "cuda" if torch.cuda.is_available() else "cpu"
        print(f"[CLASSIFIER INFO] Initializing ResNet-50 on device: {self.device}")
        
        self.model = models.resnet50(pretrained=False)
        
        self.model.fc = torch.nn.Linear(self.model.fc.in_features, 10)
        
        try:
            self.model.load_state_dict(torch.load(weights_path, map_location=self.device))
            print(f"[CLASSIFIER SUCCESS] Weights loaded successfully from {weights_path}")
        except Exception as e:
            print(f"[CLASSIFIER WARNING] Could not load weights from {weights_path}: {e}")
            print("[CLASSIFIER WARNING] Running with uninitialized weights. Make sure the file exists.")
            
        self.model.to(self.device)
        self.model.eval() 
        self.transform = transforms.Compose([
            transforms.Resize((224, 224)), 
            transforms.ToTensor(),
            transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225])
        ])
        
        self.class_names = [
            "Annual Crop",
            "Forest",
            "Herbaceous Vegetation",
            "Highway",
            "Industrial",
            "Pasture",
            "Permanent Crop",
            "Residential",
            "River",
            "Sea Lake"
        ]

    def predict_patch(self, cropped_rgb_array):
        
        if cropped_rgb_array is None or cropped_rgb_array.size == 0:
            return "Unknown", 0.0
            
        try:
            pil_img = Image.fromarray(cropped_rgb_array.astype('uint8'), 'RGB')
            
            input_tensor = self.transform(pil_img).unsqueeze(0).to(self.device)
            
            with torch.no_grad():
                outputs = self.model(input_tensor)
                
                probabilities = torch.nn.functional.softmax(outputs[0], dim=0)
                
                confidence, class_idx = torch.max(probabilities, dim=0)
                
                idx = class_idx.item()
                predicted_label = self.class_names[idx]
                confidence_score = float(confidence.item() * 100)
                
                return predicted_label, round(confidence_score, 2)
                
        except Exception as e:
            print(f"[CLASSIFIER ERROR] Error during prediction: {e}")
            return "Prediction Error", 0.0