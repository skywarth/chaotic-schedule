<?php

namespace Skywarth\ChaoticSchedule\Providers;

use Illuminate\Support\ServiceProvider;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;


class RNGFactoryServiceProvider extends ServiceProvider
{



    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RNGFactory::class, function($app,$parameters) {
            $slug=$parameters['slug']??config('chaotic-schedule.rng_engine.active_engine_slug');
            return new RNGFactory($slug);
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
