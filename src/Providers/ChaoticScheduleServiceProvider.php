<?php

namespace Skywarth\ChaoticSchedule\Providers;

use Illuminate\Support\ServiceProvider;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;


class ChaoticScheduleServiceProvider extends ServiceProvider
{



    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'chaotic-schedule');
       /* $this->app->bind(ChaoticSchedule::class, function($app) {
            return new ChaoticSchedule();
        });*/
        $this->app->bind(ChaoticSchedule::class);//TODO: Singleton perhaps





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
                __DIR__ . '/../../config/config.php' => config_path('chaotic-schedule.php'),
            ], 'config');

        }
        app(ChaoticSchedule::class)->registerMacros();

    }
}
