<?php

namespace App\Providers;

use App\Models\Booking;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
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
        RateLimiter::for('custom-booking-limit', function ($request) {
            $user = $request->user();

            if ($user && $user->role === 'super admin') {
                //admin get more capacity
                return Limit::none();
            }

            if ($user && $user->role === 'admin') {
                //admin get more capacity
                return Limit::perMinute(3)->by($user->id);
            }
            // RateLimiter::increment('custom-booking-limit:' . $user->id);

            //Guest or normal users get fewer requets
            return Limit::perMinute(1)->by(optional($user)->id ?: $request->ip());
        });

        Route::bind('booking', function ($value) {
            return Booking::findOrFail($value);
        });
    }
}
