<?php

use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\AnnotationClassController;
use App\Http\Controllers\AnnotationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PythonBridgeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', ProjectController::class);

    Route::prefix('projects/{project}')->group(function () {
        Route::post('/images/upload', [ImageUploadController::class, 'upload'])->name('projects.images.upload');
        Route::get('/annotate/{imageUpload}', [ImageUploadController::class, 'annotate'])->name('projects.annotate');

        Route::post('/classes', [AnnotationClassController::class, 'store'])->name('projects.classes.store');
        Route::delete('/classes/{annotationClass}', [AnnotationClassController::class, 'destroy'])->name('projects.classes.destroy');

        Route::post('/annotations', [AnnotationController::class, 'store'])->name('projects.annotations.store');
        Route::get('/annotations', [AnnotationController::class, 'index'])->name('projects.annotations.index');

        Route::post('/segment', [PythonBridgeController::class, 'segment'])->name('projects.segment');
        Route::post('/classify', [PythonBridgeController::class, 'classify'])->name('projects.classify');
        Route::post('/analyze-health', [PythonBridgeController::class, 'analyzeHealth'])->name('projects.analyze-health');

        Route::get('/health-report', [DashboardController::class, 'healthReport'])->name('projects.health-report');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
