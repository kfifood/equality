<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Kalibrasi;
use App\Observers\KalibrasiObserver;

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
        Kalibrasi::observe(KalibrasiObserver::class);
    }
}