<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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
        RateLimiter::for('login', function (Request $request): array {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            ];
        });

        View::composer('*', function ($view) {
            $currentUser = auth()->user();

            $unreadCount = $currentUser ? $currentUser->unreadNotifications()->count() : 0;

            $view->with([
                'hasNotifications' => $unreadCount > 0,
                'unreadNotificationsCount' => $unreadCount,
            ]);
        });
    }
}
