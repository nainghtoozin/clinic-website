<?php

use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

// API routes require authentication.
// Role management also requires appropriate permissions.
Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::apiResource('roles', RoleController::class);
});
