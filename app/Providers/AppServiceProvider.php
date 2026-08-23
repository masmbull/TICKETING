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
        // Production sits behind TLS (Cloudflare); force generated URLs to
        // https so cookies/redirects are never downgraded. Local dev keeps HTTP.
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Models\Ticket::observe(\App\Observers\TicketObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\SubCategory::observe(\App\Observers\SubCategoryObserver::class);
        \App\Models\TicketComment::observe(\App\Observers\TicketCommentObserver::class);
        \App\Models\SlaPolicy::observe(\App\Observers\SlaPolicyObserver::class);
        \App\Models\SlaMapping::observe(\App\Observers\SlaMappingObserver::class);
    }
}
