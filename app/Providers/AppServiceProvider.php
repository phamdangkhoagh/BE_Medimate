<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

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
        config(['app.timezone' => env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh')]);

        date_default_timezone_set(config('app.timezone'));
        Carbon::setLocale('vi');
    }
}
