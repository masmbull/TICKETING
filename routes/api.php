<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CategoryController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search', [TicketController::class, 'search'])->name('api.search');
});

// Public read-only endpoints (no auth required for subcategory lookups)
Route::get('/subcategories', [TicketController::class, 'subcategories'])->name('api.subcategories');

Route::middleware('auth')->group(function () {
    Route::get('/categories/{category}/subcategories', [CategoryController::class, 'getSubcategories'])->name('api.category.subcategories');
});
