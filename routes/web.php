<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SlaPolicyController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('welcome');
Route::get('/login/{role?}', [AuthController::class, 'showLoginForm'])->name('login');
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
        Route::patch('/tickets/{id}/assign', [TicketController::class, 'updateAssignee'])->name('tickets.assign');
        Route::post('/tickets/{id}/assign-to-me', [TicketController::class, 'assignToMe'])->name('tickets.assign-me');
        Route::post('/tickets/{id}/take', [TicketController::class, 'takeTicket'])->name('tickets.take');
        Route::post('/tickets/{id}/submit-analysis', [TicketController::class, 'submitAnalysis'])->name('tickets.submit-analysis');
        Route::post('/tickets/{id}/complete', [TicketController::class, 'completeTicket'])->name('tickets.complete');
        Route::patch('/tickets/{id}/priority', [TicketController::class, 'updatePriority'])->name('tickets.priority');
        Route::patch('/tickets/{id}/sla', [TicketController::class, 'updateSla'])->name('tickets.sla');
        Route::patch('/tickets/{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
        Route::get('/api/search', [TicketController::class, 'search'])->name('api.search');
        Route::get('/tickets/{id}/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])->name('tickets.attachments.download');
        Route::get('/tickets/{id}/attachments/{attachment}/serve', [TicketController::class, 'serveAttachment'])->name('tickets.attachments.serve');
        Route::delete('/tickets/{id}/attachments/{attachment}', [TicketController::class, 'destroyAttachment'])->name('tickets.attachments.destroy');

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

        // User search for mention autocomplete (public to authenticated users)
        Route::get('/api/users/search', [UserController::class, 'search'])->name('api.users.search');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/api/categories/{id}/subcategories', [CategoryController::class, 'subcategories'])->name('categories.subcategories');
        Route::post('/subcategories', [CategoryController::class, 'storeSubCategory'])->name('subcategories.store');
        Route::patch('/subcategories/{id}', [CategoryController::class, 'updateSubCategory'])->name('subcategories.update');
        Route::delete('/subcategories/{id}', [CategoryController::class, 'destroySubCategory'])->name('subcategories.destroy');

        // SLA Policies (Admin & Manager)
        Route::middleware('manager_or_admin')->prefix('settings/sla-policies')->name('sla-policies.')->group(function () {
            Route::get('/', [SlaPolicyController::class, 'index'])->name('index');
            Route::post('/', [SlaPolicyController::class, 'store'])->name('store');
            Route::patch('/{id}', [SlaPolicyController::class, 'update'])->name('update');
            Route::delete('/{id}', [SlaPolicyController::class, 'destroy'])->name('destroy');
            Route::post('/mapping', [SlaPolicyController::class, 'storeMapping'])->name('mapping.store');
            Route::delete('/mapping/{id}', [SlaPolicyController::class, 'destroyMapping'])->name('mapping.destroy');
        });

        // Audit Logs (Admin only)
        Route::middleware('admin')->prefix('audit-logs')->name('audit.')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
            Route::get('/{log}', [AuditLogController::class, 'show'])->name('show');
            Route::get('/export/excel', [AuditLogController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/csv', [AuditLogController::class, 'exportCsv'])->name('export.csv');
        });

        // Reports (Admin + Manager + Staff)
        Route::middleware('admin_manager_or_staff')->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
        });

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

        // Notifications
        Route::get('/notifications', fn () => redirect()->route('dashboard'));
        Route::patch('/notifications/mark-read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back();
        })->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', function () {
            auth()->user()->notifications->markAsRead();
            return back();
        })->name('notifications.mark-all-read');
        Route::post('/notifications/{id}/read', function ($id) {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            if ($notification->data['url'] ?? null) {
                return redirect($notification->data['url']);
            }
            return back();
        })->name('notifications.read');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});