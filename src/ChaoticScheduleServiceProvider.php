<?php

namespace Skywarth\ChaoticSchedule;

use Illuminate\Support\ServiceProvider;


class ChaoticScheduleServiceProvider extends ServiceProvider
{



    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'chaotic-schedule');
        $this->app->bind(ChaoticSchedule::class, function($app) {
            return new ChaoticSchedule();
        });




    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('chaotic-schedule'),
            ], 'config');

        }
        app(ChaoticSchedule::class)->registerMacros();

    }
}
