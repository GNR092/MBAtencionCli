<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use App\Models\User;
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
        if (!$currentUser && session('user')) {
            $currentUser = User::find(session('user')->id);
        }

        $unreadCount = $currentUser ? $currentUser->unreadNotifications()->count() : 0;

        $view->with([
            'hasNotifications' => $unreadCount > 0,
            'unreadNotificationsCount' => $unreadCount,
        ]);

        
    });
    }


    
}
