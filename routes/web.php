<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SlaPolicyController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Force password change (must be inside auth, outside force.password.change middleware)
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.update');

    // All other routes require force password change check
    Route::middleware('force.password.change')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // All Tickets (Admin/Manager)
        Route::get('/tickets', [TicketController::class, 'allTickets'])->name('tickets.all');

        // My Tickets
        Route::get('/my-tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/my-tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/my-tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/my-tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
        Route::patch('/my-tickets/{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
        Route::post('/my-tickets/{id}/comments', [TicketController::class, 'storeComment'])->name('tickets.comments.store');
        Route::get('/api/subcategories', [TicketController::class, 'subcategories'])->name('api.subcategories');
        Route::get('/api/category-priority', [TicketController::class, 'categoryPriority'])->name('api.category-priority');

        // Assigned Tickets (Staff)
        Route::get('/assigned-tickets', [TicketController::class, 'assignedTickets'])->name('tickets.assigned');

        // User Management (Admin only)
        Route::middleware('admin')->prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::patch('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::get('/api/categories/{id}/subcategories', [CategoryController::class, 'subcategories'])->name('categories.subcategories');
        Route::post('/subcategories', [CategoryController::class, 'storeSubCategory'])->name('subcategories.store');
        Route::patch('/subcategories/{id}', [CategoryController::class, 'updateSubCategory'])->name('subcategories.update');

        // SLA Policies (Admin only)
        Route::middleware('admin')->prefix('settings/sla-policies')->name('sla-policies.')->group(function () {
            Route::get('/', [SlaPolicyController::class, 'index'])->name('index');
            Route::post('/', [SlaPolicyController::class, 'store'])->name('store');
            Route::patch('/{id}', [SlaPolicyController::class, 'update'])->name('update');
            Route::delete('/{id}', [SlaPolicyController::class, 'destroy'])->name('destroy');
        });

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});