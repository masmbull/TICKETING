<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Ticket::observe(\App\Observers\TicketObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\SubCategory::observe(\App\Observers\SubCategoryObserver::class);
    }
}
