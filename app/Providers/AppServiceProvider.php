<?php

namespace App\Providers;

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
