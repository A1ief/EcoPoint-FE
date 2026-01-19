<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\RubbishController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\TestController;
use Illuminate\Container\Attributes\Auth;

// PUBLIC ROUTES
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authentication routes
Route::get('/login', [AuthController::class, 'showlogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route::middleware(['auth'])->group(function () {
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// User management
Route::resource('users', UserController::class);
Route::prefix('users')->group(function () {
    Route::get('{id}/edit-password', [UserController::class, 'editPassword'])->name('users.edit-password');
    Route::put('{id}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');
});

// For superadmin only
Route::middleware(['role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Points routes
    Route::get('/point', [PointController::class, 'index'])->name('point.index');
    Route::get('/point/create', [PointController::class, 'create'])->name('point.create');
    Route::post('/point', [PointController::class, 'store'])->name('point.store');
    Route::get('/point/{id}/edit', [PointController::class, 'edit'])->name('point.edit');
    Route::put('/point/{id}', [PointController::class, 'update'])->name('point.update');
    Route::delete('/point/{id}', [PointController::class, 'destroy'])->name('point.destroy');
    Route::post('/point/{id}/approve', [PointController::class, 'approve'])->name('point.approve');
    Route::post('/point/{id}/reject', [PointController::class, 'reject'])->name('point.reject');
});

// routes/web.php
Route::get('/test-api', [UserController::class, 'testConnection']);
