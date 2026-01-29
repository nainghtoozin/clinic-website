<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use phpDocumentor\Reflection\Location;

Route::prefix('/')->group(function () {
    Route::get('/', [PublicController::class, 'index'])->name('public.index');
    Route::get('/error', [PublicController::class, 'error'])->name('public.error');
    Route::get('/about', [PublicController::class, 'about'])->name('public.about');
    //Route::get('/appointment', [PublicController::class, 'appointment'])->name('public.appointment');
    Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
    Route::get('/department_details', [PublicController::class, 'department_details'])->name('public.department_details');
    Route::get('/department', [PublicController::class, 'department'])->name('public.department');
    Route::get('/doctor-list', [PublicController::class, 'doctors'])->name('public.doctor-list');
    Route::get('/faq', [PublicController::class, 'faq'])->name('public.faq');
    Route::get('/gallery', [PublicController::class, 'gallery'])->name('public.gallery');
    Route::get('/gallery_details', [PublicController::class, 'gallery_details'])->name('public.gallery_details');
    Route::get('/privacy', [PublicController::class, 'privacy'])->name('public.privacy');
    Route::get('/service_details', [PublicController::class, 'service_details'])->name('public.service_details');
    Route::get('/service', [PublicController::class, 'services'])->name('public.services');
    Route::get('/starter_page', [PublicController::class, 'starter_page'])->name('public.starter_page');
    Route::get('/terms', [PublicController::class, 'terms'])->name('public.terms');
    Route::get('/testimonial', [PublicController::class, 'testimonial'])->name('public.testimonial');
});

Route::get('/appointments/create', [AppointmentController::class, 'create'])
    ->name('appointments.create');

Route::post('/appointments', [AppointmentController::class, 'store'])
    ->name('appointments.store');


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('doctors', DoctorController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('services', ServiceController::class);

    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/ajax', [AppointmentController::class, 'ajaxIndex'])->name('appointments.ajax');
    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/website', [SettingController::class, 'edit'])->name('settings.website.edit');

    Route::post('settings/website', [SettingController::class, 'update'])->name('settings.website.update');
});

require __DIR__ . '/auth.php';
