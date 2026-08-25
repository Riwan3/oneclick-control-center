<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// GET /login serves the SPA shell (Vue Router renders Login.vue) and is
// named 'login' purely so Laravel's auth middleware has a valid redirect
// target if it's ever invoked for a non-JSON request. POST /login is the
// actual credential submission endpoint.
Route::get('/login', function () {
    return view('welcome');
})->name('login');

Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth');

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/user', [UserController::class, 'show']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::patch('/users/{user}', [UserManagementController::class, 'update']);
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);
    });

    Route::middleware('role:super_admin,management')->group(function () {
        Route::get('/audit-logs', [AuditController::class, 'index']);
    });
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
