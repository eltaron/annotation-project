<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Project count: ' . App\Models\Project::count() . "\n";
echo 'Image count: ' . App\Models\ImageUpload::count() . "\n";
foreach (App\Models\ImageUpload::all() as $im) {
    echo 'ID:' . $im->id . ' Path:' . ($im->file_path ?? 'null') . "\n";
}
