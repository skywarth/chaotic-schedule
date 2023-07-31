<?php

namespace Skywarth\ChaoticSchedule\Providers;

use Illuminate\Support\ServiceProvider;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;


class SeedGenerationServiceProvider extends ServiceProvider
{



    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SeedGenerationService::class, function($app) {
            return new SeedGenerationService();
        });




    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {


    }
}
