<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RubbishController;
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
});
// });

// routes/web.php
Route::get('/test-api', [UserController::class, 'testConnection']);

Route::get('/rubbish', [RubbishController::class, 'index'])->name('rubbish');
Route::get('/rubbishCreate', [RubbishController::class, 'create'])->name('rubbishCreate');
Route::post('/rubbishStore', [RubbishController::class, 'store'])->name('rubbishStore');
Route::get('/rubbishEdit/{id}', [RubbishController::class, 'edit'])->name('rubbishEdit');
Route::put('/rubbishUpdate/{id}', [RubbishController::class, 'update'])->name('rubbishUpdate');
Route::delete('/rubbishDestroy/{id}', [RubbishController::class, 'destroy'])->name('rubbishDestroy');
Route::get('/point', [TestController::class, 'point'])->name('point');
Route::get('/pointCreate', [TestController::class, 'pointCreate'])->name('pointCreate');
