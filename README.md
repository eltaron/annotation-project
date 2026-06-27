# 🌍 GeoLens — نظام ذكاء اصطناعي لتحليل وتصنيف الصور الفضائية

**GeoLens** هو نظام متكامل لتحليل الصور الفضائية (Satellite Imagery) باستخدام تقنيات **التعلم العميق (Deep Learning)** و **الرؤية الحاسوبية (Computer Vision)**. يدعم النظام **التقسيم الدلالي (Semantic Segmentation)**، **تصنيف الأجسام (Classification)**، **تحليل صحة المحاصيل (Crop Health Analysis)**، و**إدارة المشاريع** عبر واجهة ويب تفاعلية.

---

## 📋 فهرس المحتويات

- [المميزات الرئيسية](#-المميزات-الرئيسية)
- [البنية التقنية (Architecture)](#-البنية-التقنية-architecture)
- [متطلبات التشغيل](#-متطلبات-التشغيل)
- [طريقة التثبيت والتشغيل](#-طريقة-التثبيت-والتشغيل)
- [المشروع خطوة بخطوة](#-المشروع-خطوة-بخطوة)
- [قاعدة البيانات](#-قاعدة-البيانات)
- [واجهة API](#-واجهة-api)
- [Python AI Scripts](#-python-ai-scripts)
- [المشاكل المعروفة](#-المشاكل-المعروفة)
- [الخطوات القادمة](#-الخطوات-القادمة)

---

## ✨ المميزات الرئيسية

| الميزة | الوصف |
|--------|-------|
| 🔐 **نظام Authentication** | تسجيل دخول / إنشاء حساب باستخدام Laravel Breeze |
| 📁 **إدارة المشاريع** | إنشاء، تعديل، حذف مشاريع — كل مشروع له صوره وكلاساته الخاصة |
| 📸 **رفع الصور الفضائية** | رفع صور `.tif` متعددة الباندات (4 باند كحد أدنى: R, G, B, NIR) |
| 🏷️ **إدارة كلاسات التصنيف** | إضافة كلاسات (مثلاً: Building, Vegetation, Water, Road) لكل مشروع |
| 🎯 **Semantic Segmentation (SAM)** | تقسيم الصورة إلى أجزاء باستخدام **Segment Anything Model (SAM)** من Meta AI |
| 🤖 **تصنيف الأجسام (ResNet-50)** | تصنيف كل جزء تم تحديده باستخدام ResNet-50 مدرب على **EuroSAT** (10 فئات، دقة 97.7%) |
| 🌱 **تحليل صحة المحاصيل** | حساب NDVI + إحصائيات متقدمة (نسبة صحية/مجهد/غير صحي) + رسم بياني |
| 📊 **لوحة التحكم (Dashboard)** | إحصائيات عامة: عدد المشاريع، الصور، التصنيفات، التقارير الصحية |
| 📈 **تقارير صحية** | Pie Chart + Bar Chart باستخدام Chart.js مع تفاصيل كل محصول |
| 🗺️ **GeoTIFF Metadata** | استخراج Width, Height, Bands, CRS تلقائياً باستخدام Rasterio |

---

## 🏗️ البنية التقنية (Architecture)

```
annotation-project/
│
├── backend/                          # Laravel 12 (PHP 8.2)
│   ├── app/
│   │   ├── Http/Controllers/         # 7 Controllers (Web + API Bridge)
│   │   ├── Models/                   # 5 Models (Eloquent ORM)
│   │   └── ...
│   ├── resources/views/              # 12 Blade view (Tailwind CSS + Chart.js)
│   ├── routes/web.php                # 17 route (Auth + CRUD + Python Bridge)
│   ├── database/migrations/          # 9 Migrations (5 مخصصة للمشروع)
│   └── ...
│
├── geo_processor.py                  # NDVI + Heatmap + إحصائيات الباندات
├── sam__predectorr.py                # SAM Segmenter (click-based segmentation)
├── classifier.py                     # ResNet-50 Classifier (EuroSAT، 10 فئات)
├── projects_manager.py               # إدارة المشاريع على نظام الملفات
├── sam_update.py                     # تحديث SAM مع COG pipeline (optional)
├── sam_test.py                       # اختبار SAM
│
└── checkpoint/                       # أوزان النماذج (models weights)
    ├── classifier_weights.pth        # أوزان ResNet-50 (~94 MB) ✅ موجود
    └── sam_vit_b_01ec64.pth          # أوزان SAM (~375 MB) ❌ مفقود
```

### Stack المستخدمة:

| الطبقة | التقنية |
|--------|---------|
| **Backend Framework** | Laravel 12 (PHP 8.2.20) |
| **Frontend** | Blade + Tailwind CSS + Chart.js + jQuery |
| **Build Tool** | Vite 7 |
| **Database** | MySQL (annotation_db) |
| **AI/ML** | Python 3.8.8 + PyTorch (CPU) + Rasterio + Segment Anything |
| **Authentication** | Laravel Breeze (Blade Stack) |

---

## 📦 متطلبات التشغيل

### مطلوب أساسي:
- **PHP** ≥ 8.1 (موجود: 8.2.20)
- **Composer** (مدير حزم PHP)
- **Node.js** ≥ 18 (موجود)
- **MySQL** (موجود على `127.0.0.1:3306`)
- **Python** 3.8+ (موجود: 3.8.8)

### مطلوب لـ Python AI:
```bash
pip install torch torchvision --index-url https://download.pytorch.org/whl/cpu
pip install rasterio numpy pillow matplotlib opencv-python
```

### مطلوب لـ SAM Segmentation:
- ملف الأوزان `sam_vit_b_01ec64.pth` (~375 MB) من:
  ```
  https://dl.fbaipublicfiles.com/segment_anything/sam_vit_b_01ec64.pth
  ```
  ضعه في `checkpoint/sam_vit_b_01ec64.pth`

### اختياري:
- **GDAL** (لتشغيل COG pipeline في `sam_update.py`)

---

## 🚀 طريقة التثبيت والتشغيل

### 1. تحميل المشروع
```bash
git clone https://github.com/<your-username>/<repo-name>.git
cd annotation-project
```

### 2. تشغيل Laravel Backend
```bash
cd backend

# تثبيت حزم PHP
composer install

# تثبيت حزم JavaScript
npm install

# بناء الـ assets (CSS/JS)
npm run build

# إعداد ملف البيئة
copy .env.example .env
```

عدّل ملف `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=annotation_db
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# إنشاء مفتاح التشفير
php artisan key:generate

# إنشاء قاعدة البيانات (في MySQL أو phpMyAdmin)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS annotation_db"

# تشغيل الترحيلات (migrations)
php artisan migrate

# إنشاء رابط التخزين للصور المرفوعة
php artisan storage:link

# تشغيل السيرفر
php artisan serve
```

### 3. تشغيل Vite للتطوير (اختياري — في تيرمينال منفصل)
```bash
cd backend
npm run dev
```

### 4. فتح المتصفح
```
http://127.0.0.1:8000/
```

### 5. تنزيل SAM Checkpoint (لتشغيل Segmentation)
```
https://dl.fbaipublicfiles.com/segment_anything/sam_vit_b_01ec64.pth
→ ضع الملف في: annotation-project/checkpoint/sam_vit_b_01ec64.pth
```

---

## 🧭 المشروع خطوة بخطوة

### Welcome Page → `GET /`
<img src="prototype demo/Screenshot 2026-06-20 210126.png" width="600" alt="Welcome Page">

صفحة ترحيبية بتصميم **GeoLens** — اسم المشروع، شرح مختصر، وزر **Get Started** للتسجيل.

### Authentication → `GET /login` | `GET /register`
<img src="prototype demo/Screenshot 2026-06-20 210229.png" width="600" alt="Register">

نظام تسجيل دخول وإنشاء حساب باستخدام **Laravel Breeze** (Blade Stack).

### إنشاء مشروع → `GET /projects/create` | `POST /projects`
<img src="prototype demo/Screenshot 2026-06-20 210743.png" width="600" alt="Projects">

إنشاء مشروع جديد: الاسم + وصف (اختياري). كل مشروع يحتوي على:
- **صور فضائية** (`.tif` files)
- **كلاسات تصنيف** (مثلاً: Building, Vegetation, Water, Road)
- **Annotations** (التقسيمات والتصنيفات لكل صورة)

### عرض المشروع + رفع صورة → `GET /projects/{id}`
<img src="prototype demo/Screenshot 2026-06-20 210928.png" width="600" alt="Project Details">

- معلومات المشروع
- جدول الصور المرفوعة (مع Width, Height, Bands, CRS)
- رفع صور `.tif` جديدة
- إدارة كلاسات التصنيف (إضافة/حذف)
- زر **Annotate** لكل صورة
- زر **Health Report** لتقرير صحة المحاصيل

### Annotation Workspace → `GET /projects/{project}/annotate/{image}`
- عرض الصورة على Canvas
- اختيار Class من القائمة
- الضغط على الصورة لتشغيل **SAM Segmentation** عبر AJAX
- عرض الـ mask الناتج على الصورة
- **Undo** آخر segmentation
- **Zoom In / Zoom Out**
- **تصنيف الجزء المحدد** باستخدام ResNet-50
- **Export GeoJSON** لتحميل الـ annotations
- **Analyze Health** لتحليل صحة المحاصيل في المنطقة المحددة

### تقرير صحة المحاصيل → `GET /projects/{project}/health-report`
- Pie Chart يوضح: Healthy %, Stressed %, Unhealthy %
- Bar Chart لمقارنة المساحات
- جدول بالتقارير السابقة (Total Area, Healthy, Stressed, Unhealthy)
- زر **حذف** لتقارير قديمة

### Dashboard → `GET /dashboard`
- بطاقات إحصائية: عدد المشاريع، الصور، التصنيفات، التقارير
- جدول بآخر 5 مشاريع
- **Pie Chart** لحالة المشاريع
- **Bar Chart** للمشاريع حسب عدد الصور

---

## 🗄️ قاعدة البيانات

```sql
-- annotation_db (MySQL)

users                    -- id, name, email, password
projects                 -- id, user_id, name, description
image_uploads            -- id, project_id, user_id, original_name, file_path,
                         --    file_size, width, height, bands, crs
annotation_classes       -- id, project_id, name, color
annotations              -- id, image_upload_id, annotation_class_id, user_id,
                         --    mask_data (BLOB), polygon_coordinates (JSON),
                         --    bbox, area_pixels, area_m2,
                         --    classification_label, classification_confidence,
                         --    geo_metadata (JSON)
crop_health_results      -- id, project_id, image_upload_id,
                         --    total/healthy/stressed/unhealthy area (m²),
                         --    healthy/stressed/unhealthy_percentage,
                         --    overall_status, raw_stats (JSON)
```

**العلاقات (Relationships):**
- `User` → hasMany `projects`, `image_uploads`, `annotations`
- `Project` → hasMany `image_uploads`, `annotation_classes`, `crop_health_results`
- `ImageUpload` → hasMany `annotations`
- `AnnotationClass` → hasMany `annotations`

---

## 🌐 واجهة API

### Web Routes (17 route):

| Method | Route | Controller | الهدف |
|--------|-------|-----------|-------|
| GET | `/` | WelcomeController | الصفحة الترحيبية |
| GET/POST | `/login` | Auth | تسجيل الدخول |
| GET/POST | `/register` | Auth | إنشاء حساب |
| GET | `/dashboard` | DashboardController | لوحة التحكم |
| GET | `/projects` | ProjectController@index | قائمة المشاريع |
| GET | `/projects/create` | ProjectController@create | إنشاء مشروع |
| POST | `/projects` | ProjectController@store | حفظ مشروع |
| GET | `/projects/{id}` | ProjectController@show | عرض المشروع |
| GET | `/projects/{id}/edit` | ProjectController@edit | تعديل المشروع |
| DELETE | `/projects/{id}` | ProjectController@destroy | حذف المشروع |
| POST | `/projects/{id}/images/upload` | ImageUploadController@upload | رفع صورة |
| GET | `/projects/{id}/annotate/{image}` | ImageUploadController@annotate | مساحة العمل |
| POST | `/projects/{id}/classes` | AnnotationClassController@store | إضافة class |
| DELETE | `/projects/{id}/classes/{class}` | AnnotationClassController@destroy | حذف class |
| POST | `/projects/{id}/segment` | PythonBridgeController@segment | تشغيل SAM |
| POST | `/projects/{id}/classify` | PythonBridgeController@classify | تشغيل ResNet-50 |
| POST | `/projects/{id}/analyze-health` | PythonBridgeController@analyzeHealth | تحليل NDVI |
| GET | `/projects/{id}/health-report` | DashboardController@healthReport | تقرير الصحة |

### مسار AJAX (من Annotation Workspace):
```javascript
// تشغيل SAM Segmentation
POST /projects/{id}/segment
Body: { image_path, x, y }
→ Response: { mask_path, polygon_coordinates, ... }

// تصنيف الجزء المحدد
POST /projects/{id}/classify
Body: { image_path, x, y }
→ Response: { label, confidence }

// تحليل صحة المحاصيل
POST /projects/{id}/analyze-health
Body: { image_path }
→ Response: { healthy_percentage, stressed_percentage, ... }

// حفظ Annotation
POST /projects/{id}/annotations
Body: { image_upload_id, annotation_class_id, polygon_coordinates, ... }
→ JSON response

// حذف Annotation
POST /projects/{id}/annotations
Header: X-HTTP-Method-Override: DELETE
```

---

## 🐍 Python AI Scripts

### `geo_processor.py` — معالج GeoTIFF
```python
from geo_processor import process_geotiff

stats = process_geotiff("image.tif")
# { 
#   "ndvi_mean": 0.34, "ndvi_std": 0.12,
#   "red_mean": 120.5, "nir_mean": 200.3,
#   "rgb_path": "output_rgb.jpg",
#   "heatmap_path": "output_ndvi_heatmap.jpg"
# }
```
- **4 bands**: Red, Green, Blue, NIR
- **NDVI** = (NIR - Red) / (NIR + Red)
- **Heatmap**: OpenCV colormap (JET) على الـ NDVI

### `sam__predectorr.py` — SAM Segmenter
```python
from sam__predectorr import AdvancedSAMSegmenter

segmenter = AdvancedSAMSegmenter()
result = segmenter.segment("image.tif", x=500, y=300)
# { mask, polygon_coordinates, bbox, area_pixels, area_m2 }
```
- يستخدم **Segment Anything Model (SAM)** من Meta AI
- Segmentation بنقرة واحدة (click-based)
- يستخرج الـ polygon والـ bbox
- يستورد GeoJSON مع إحداثيات جغرافية
- **يحتاج checkpoint**: `checkpoint/sam_vit_b_01ec64.pth`

### `classifier.py` — ResNet-50 Classifier
```python
from classifier import EuroSATResNetClassifier

clf = EuroSATResNetClassifier()
label, confidence = clf.classify_patch("patch.jpg")
# label: "Residential", confidence: 0.977
```
- **ResNet-50** (torchvision.models.resnet50)
- مدرب مسبقاً على **EuroSAT** (10 فئات)
  - `AnnualCrop`, `Forest`, `HerbaceousVegetation`, `Highway`,
    `Industrial`, `Pasture`, `PermanentCrop`, `Residential`,
    `River`, `SeaLake`
- دقة: **97.7%**
- وزنه موجود: `checkpoint/classifier_weights.pth` (~94 MB)

### `projects_manager.py` — إدارة المشاريع
- إنشاء/قراءة/تعديل/حذف المشاريع على نظام الملفات (JSON)
- كل مشروع له: name, description, images, classes

### `sam_update.py` — تحديث SAM (اختياري)
- COG (Cloud Optimized GeoTIFF) pipeline
- تحويل الصور إلى COG باستخدام GDAL
- دمج الأجزاء المتجاورة

---

## ⚠️ المشاكل المعروفة

### 🔴 Blocker — SAM Checkpoint مفقود
```
ملف sam_vit_b_01ec64.pth (~375 MB) needed to run sam__predectorr.py
```
**الحل:** حمّله من:
```
https://dl.fbaipublicfiles.com/segment_anything/sam_vit_b_01ec64.pth
```
وضعه في `checkpoint/sam_vit_b_01ec64.pth`

### 🔴 Blocker — GDAL غير مثبت على Windows
`sam_update.py` يحاول إنشاء `/tmp/cog_outputs` (مسار Linux) ويحتاج GDAL.

### 🟡 Warning — Deprecated API في classifier.py
```python
# يستخدم pretrained=False (deprecated في torchvision 0.19)
# و torch.load بدون weights_only=True
```
يحتاج تحديث بسيط للتوافق مع الإصدارات الأحدث.

### 🟡 Warning — Python env annotation_env/ path broken
Virtual environment مساره مش موجود — يتم استخدام Python الافتراضي.

### 🟡 Warning — Streamlit app
`app.py` (Streamlit) لسه شغال بعد تثبيت dependencies لكنه قديم مقارنة بـ Laravel UI.

---

## 🧪 اختبار التشغيل

### اختبار PHP:
```bash
cd backend
php artisan serve
# زيارة http://127.0.0.1:8000/ → يجب رؤية Welcome page
```

### اختبار جميع الـ Routes (كلها ترجع 200 ✅):
```
GET  /                 → 200 (Welcome Page)
GET  /login            → 200 (Login Page)
GET  /register         → 200 (Register Page)
GET  /dashboard        → 200 (Dashboard — يعيد توجيه لـ Login لو مش مسجل)
GET  /projects         → 200 (Projects List)
GET  /projects/create  → 200 (Create Project Form)
GET  /forgot-password  → 200 (Forgot Password)
```

### اختبار Python AI:
```bash
# اختبار geo_processor.py (يحتاج صورة .tif حقيقية 4 باند)
python geo_processor.py

# اختبار classifier.py
python -c "from classifier import EuroSATResNetClassifier; c = EuroSATResNetClassifier(); print('✅ Classifier loaded')"
# Output: ✅ Classifier loaded
# (يتطلب وجود checkpoint/classifier_weights.pth)

# اختبار sam__predectorr.py (يحتاج checkpoint)
python -c "from sam__predectorr import AdvancedSAMSegmenter; s = AdvancedSAMSegmenter(); print('✅ SAM loaded')"
# Output: ❌ FileNotFoundError إذا checkpoint مش موجود
```

### اختبار قاعدة البيانات:
```bash
cd backend
php artisan tinker
> \App\Models\User::count()
> \App\Models\Project::count()
> \App\Models\ImageUpload::count()
```

---

## 📈 الخطوات القادمة

1. **⬇️ تحميل SAM Checkpoint** — تنزيل `sam_vit_b_01ec64.pth` (~375MB) وتشغيل Segmentation
2. **🧪 اختبار شامل** — رفع صورة .tif حقيقية وتجربة كل المراحل
3. **🐳 Docker** — إنشاء Docker Compose لتشغيل كل شيء بأمر واحد
4. **☁️ API RESTful** — تحويل الـ web routes إلى JSON API (للتكامل مع تطبيقات أخرى)
5. **📱 UI Enhancements** — تحسين الـ responsive design + dark mode
6. **🧠 Model Updates** — تحديث classifier.py لاستخدام torch.hub بدل deprecated API
7. **⚡ GPU Support** — إضافة دعم CUDA للتسريع
8. **🗂️ COG Pipeline** — تثبيت GDAL وتفعيل COG في sam_update.py

---

## 👨‍💻 المطورون

- **Version:** 1.0.0
- **PHP:** 8.2.20
- **Laravel:** 12
- **Python:** 3.8.8
- **Database:** MySQL 8.0+
- **ML Models:** SAM (Meta AI) + ResNet-50 (EuroSAT)

---

## 📄 الترخيص

هذا المشروع للأغراض التعليمية والبحثية. جميع النماذج (SAM, ResNet-50) لها تراخيصها الخاصة:
- **SAM**: [MIT License](https://github.com/facebookresearch/segment-anything/blob/main/LICENSE) — Meta AI
- **EuroSAT**: [MIT License](https://github.com/phelber/eurosat) — RGB + NIR
