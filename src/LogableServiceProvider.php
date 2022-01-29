<?php

namespace Sagar290\Logable;

use Illuminate\Support\ServiceProvider;
use Sagar290\Logable\Console\Commands\ClearLog;
use Sagar290\Logable\Console\Commands\MonitorLog;

class LogableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {



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
                MonitorLog::class,
                ClearLog::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/Config/logable.php' => config_path('logable.php'),
        ], 'config');

    }
}
