<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LearningPathController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PublicBlogController;
use App\Http\Controllers\PublicProjectController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TechnologyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/projects', [PublicProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [PublicProjectController::class, 'show'])->name('projects.show');

Route::get('/blog', [PublicBlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [PublicBlogController::class, 'show'])->name('blog.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('technologies', TechnologyController::class)->except('show');
    Route::resource('projects', ProjectController::class)->except('show');
    Route::resource('tags', TagController::class)->except('show');
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('learning-paths', LearningPathController::class)->except('show');
    Route::resource('learning-paths.lessons', LessonController::class)->scoped()->except('show');
});

require __DIR__.'/auth.php';
