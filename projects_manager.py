import os
import json
import csv
import re
import shutil
from datetime import datetime

PROJECTS_DIR = "data_projects"

if not os.path.exists(PROJECTS_DIR):
    os.makedirs(PROJECTS_DIR)


def is_valid_project_name(name):
    return bool(re.match(r'^[A-Za-z0-9_-]+$', name))


def get_projects():
    projects = []

    if not os.path.exists(PROJECTS_DIR):
        return projects

    for folder in os.listdir(PROJECTS_DIR):
        folder_path = os.path.join(PROJECTS_DIR, folder)

        if os.path.isdir(folder_path):
            meta_path = os.path.join(folder_path, "metadata.json")
            description = ""

            try:
                if os.path.exists(meta_path):
                    with open(meta_path, "r", encoding="utf-8") as f:
                        meta = json.load(f)
                        description = meta.get("description", "")
            except:
                pass

            projects.append({
                "name": folder,
                "description": description
            })

    return projects


def create_project(project_name, description=""):

    if not is_valid_project_name(project_name):
        return False

    project_path = os.path.join(PROJECTS_DIR, project_name)

    if os.path.exists(project_path):
        return False

    try:
        os.makedirs(project_path)

        now = datetime.now().isoformat()

        metadata = {
            "project_name": project_name,
            "description": description,
            "created_at": now,
            "updated_at": now,
            "classes": [],
            "annotations": []
        }

        meta_path = os.path.join(project_path, "metadata.json")

        with open(meta_path, "w", encoding="utf-8") as f:
            json.dump(metadata, f, indent=4)

        return True

    except:
        return False


def delete_project(project_name):

    project_path = os.path.join(PROJECTS_DIR, project_name)

    if not os.path.exists(project_path):
        return False

    try:
        shutil.rmtree(project_path)
        return True
    except:
        return False


def generate_csv_report(project_name, stats):

    project_path = os.path.join(PROJECTS_DIR, project_name)

    if not os.path.exists(project_path):
        return None

    csv_path = os.path.join(project_path, f"{project_name}_vegetation_report.csv")

    try:
        with open(csv_path, "w", newline="", encoding="utf-8") as f:
            writer = csv.writer(f)
            writer.writerow(["Health Status", "Coverage Percentage (%)", "Total Area (m2)"])

            for class_name, data in stats.items():
                writer.writerow([
                    class_name,
                    data.get("percentage", 0),
                    data.get("area_m2", 0)
                ])

        return csv_path

    except:
        return None


def generate_expert_export(project_name, additional_meta=None):

    project_path = os.path.join(PROJECTS_DIR, project_name)
    meta_path = os.path.join(project_path, "metadata.json")

    if not os.path.exists(meta_path):
        return None

    try:
        with open(meta_path, "r", encoding="utf-8") as f:
            data = json.load(f)

        if additional_meta:
            data["latest_analysis_summary"] = additional_meta

        export_path = os.path.join(project_path, f"{project_name}_expert_export.json")

        with open(export_path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=4)

        return export_path

    except:
        return None


def add_project_class(project_name, class_name, class_color):

    project_path = os.path.join(PROJECTS_DIR, project_name)
    meta_path = os.path.join(project_path, "metadata.json")

    if not os.path.exists(meta_path):
        return False

    try:
        with open(meta_path, "r", encoding="utf-8") as f:
            data = json.load(f)

        if any(c["name"].lower() == class_name.lower() for c in data["classes"]):
            return False

        data["classes"].append({
            "name": class_name,
            "color": class_color
        })

        data["updated_at"] = datetime.now().isoformat()

        with open(meta_path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=4)

        return True

    except:
        return False


def delete_project_class(project_name, class_name):

    project_path = os.path.join(PROJECTS_DIR, project_name)
    meta_path = os.path.join(project_path, "metadata.json")

    if not os.path.exists(meta_path):
        return False

    try:
        with open(meta_path, "r", encoding="utf-8") as f:
            data = json.load(f)

        data["classes"] = [
            c for c in data["classes"]
            if c["name"].lower() != class_name.lower()
        ]

        data["updated_at"] = datetime.now().isoformat()

        with open(meta_path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=4)

        return True

    except:
        return False
    

def update_project_statistics(project_name, class_id, stats_data):
    
    project_path = os.path.join(PROJECTS_DIR, project_name)
    meta_path = os.path.join(project_path, "metadata.json")

    if not os.path.exists(meta_path):
        return False

    try:
        with open(meta_path, "r", encoding="utf-8") as f:
            data = json.load(f)

        if "annotations" not in data:
            data["annotations"] = {}

        data["annotations"][str(class_id)] = stats_data

        data["updated_at"] = datetime.now().isoformat()

        with open(meta_path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=4, ensure_ascii=False)

        return True

    except Exception as e:
        print(f"[PROJECT MANAGER ERROR] Failed to update JSON: {e}")
        return False


def export_to_coco_format(project_name, image_filename, image_width, image_height, all_sam_stats):
    import datetime
    import os
    import json

    coco_output = {
        "info": {
            "description": f"COCO Export for project: {project_name}",
            "url": "Al-Azhar University - AI Annotation Tool",
            "version": "1.0",
            "year": datetime.datetime.now().year,
            "date_created": datetime.datetime.now().strftime("%Y/%m/%d")
        },
        "licenses": [],
        "images": [
            {
                "id": 1,
                "file_name": image_filename,
                "width": int(image_width),
                "height": int(image_height),
                "date_captured": datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            }
        ],
        "categories": [],
        "annotations": []
    }

    eurosat_classes = [
        "Annual Crop", "Forest", "Herbaceous Vegetation", "Highway", 
        "Industrial", "Pasture", "Permanent Crop", "Residential", 
        "River", "Sea Lake"
    ]

    for idx, class_name in enumerate(eurosat_classes):
        coco_output["categories"].append({
            "id": idx + 1,  
            "name": class_name,
            "supercategory": "land_cover"
        })

    class_name_to_id = {name: (i + 1) for i, name in enumerate(eurosat_classes)}

    annotation_id = 1
    
    for class_id, stats in all_sam_stats.items():
        resnet_result = stats.get("classification_results", {})
        predicted_label = resnet_result.get("predicted_label", None)
        
        category_id = class_name_to_id.get(predicted_label, int(class_id))
        
        bbox_pixels = stats.get("bbox_pixels", [100, 100, 50, 50]) 
        
        segmentation_polygons = stats.get("polygons_pixels", [])

        coco_annotation = {
            "id": annotation_id,
            "image_id": 1,
            "category_id": category_id,
            "segmentation": segmentation_polygons, 
            "area": float(stats.get("total_pixels", 0)), 
            "bbox": bbox_pixels, 
            "iscrowd": 0
        }
        coco_output["annotations"].append(coco_annotation)
        annotation_id += 1

    project_path = os.path.join(PROJECTS_DIR, project_name)
    coco_file_path = os.path.join(project_path, "coco_annotations.json")
    
    with open(coco_file_path, "w", encoding="utf-8") as f:
        json.dump(coco_output, f, indent=4, ensure_ascii=False)
        
    return coco_file_path