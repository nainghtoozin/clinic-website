<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    Route::get('/', [PublicController::class, 'index'])->name('public.index');
    Route::get('/error', [PublicController::class, 'error'])->name('public.error');
    Route::get('/about', [PublicController::class, 'about'])->name('public.about');
    Route::get('/appointment', [PublicController::class, 'appointment'])->name('public.appointment');
    Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
    Route::get('/department_details', [PublicController::class, 'department_details'])->name('public.department_details');
    Route::get('/department', [PublicController::class, 'department'])->name('public.department');
    Route::get('/doctor-list', [PublicController::class, 'doctors'])->name('public.doctor-list');
    Route::get('/faq', [PublicController::class, 'faq'])->name('public.faq');
    Route::get('/gallery', [PublicController::class, 'gallery'])->name('public.gallery');
    Route::get('/gallery_details', [PublicController::class, 'gallery_details'])->name('public.gallery_details');
    Route::get('/privacy', [PublicController::class, 'privacy'])->name('public.privacy');
    Route::get('/service_details', [PublicController::class, 'service_details'])->name('public.service_details');
    Route::get('/services', [PublicController::class, 'services'])->name('public.services');
    Route::get('/starter_page', [PublicController::class, 'starter_page'])->name('public.starter_page');
    Route::get('/terms', [PublicController::class, 'terms'])->name('public.terms');
    Route::get('/testimonial', [PublicController::class, 'testimonial'])->name('public.testimonial');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('doctors', DoctorController::class);
    Route::resource('departments', DepartmentController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
