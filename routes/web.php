<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tickets
    Route::get('/my-tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/my-tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/my-tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/my-tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::patch('/my-tickets/{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::post('/my-tickets/{id}/comments', [TicketController::class, 'storeComment'])->name('tickets.comments.store');
    Route::get('/api/subcategories', [TicketController::class, 'subcategories'])->name('api.subcategories');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::patch('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::get('/api/categories/{id}/subcategories', [CategoryController::class, 'subcategories'])->name('categories.subcategories');
    Route::post('/subcategories', [CategoryController::class, 'storeSubCategory'])->name('subcategories.store');
    Route::patch('/subcategories/{id}', [CategoryController::class, 'updateSubCategory'])->name('subcategories.update');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});