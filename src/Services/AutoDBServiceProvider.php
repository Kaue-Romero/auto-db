<?php

namespace Leivingson\AutoDB\Services;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Leivingson\AutoDB\Console\Commands\AutoDBCommands;

class AutoDBServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        View::addLocation(__DIR__.'/../Blades');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AutoDBCommands::class,
            ]);
        }
    }
}
