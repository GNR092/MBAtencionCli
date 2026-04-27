<?php

namespace Canva\HBD;

use Illuminate\Support\ServiceProvider;

class HbdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'hbd');

        $this->loadMigrationsFrom(__DIR__.'/../src/Database/Migrations');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Canva\HBD\Console\Commands\SendHbdEmails::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../src/Resources/views', 'hbd');
    }
}
