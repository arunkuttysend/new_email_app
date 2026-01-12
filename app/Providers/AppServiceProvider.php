<?php

namespace App\Providers;

use App\Models\Subscriber;
use App\Observers\SubscriberObserver;
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
        // Register observer to auto-update mailing list counts
        Subscriber::observe(SubscriberObserver::class);
    }
}
