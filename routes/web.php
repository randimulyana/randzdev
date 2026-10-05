<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\LearningPathController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PublicBlogController;
use App\Http\Controllers\PublicContactController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicLearningController;
use App\Http\Controllers\PublicProjectController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TechnologyController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicHomeController::class, 'index'])->name('home');
Route::get('/about', [PublicHomeController::class, 'about'])->name('about');

Route::get('/projects', [PublicProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [PublicProjectController::class, 'show'])->name('projects.show');

Route::get('/blog', [PublicBlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [PublicBlogController::class, 'show'])->name('blog.show');

Route::get('/learn', [PublicLearningController::class, 'index'])->name('learn.index');
Route::get('/learn/{learningPath}', [PublicLearningController::class, 'show'])->name('learn.show');
Route::get('/learn/{learningPath}/{lesson}', [PublicLearningController::class, 'lesson'])->name('learn.lesson');

Route::get('/contact', [PublicContactController::class, 'show'])->name('contact');
Route::post('/contact', [PublicContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/dashboard', function () {
    if (auth()->user()?->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('technologies', TechnologyController::class)->except('show');
    Route::resource('projects', ProjectController::class)->except('show');
    Route::resource('tags', TagController::class)->except('show');
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('learning-paths', LearningPathController::class)->except('show');
    Route::resource('learning-paths.lessons', LessonController::class)->scoped()->except(['index', 'show']);
    Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'show']);
});

require __DIR__.'/auth.php';
