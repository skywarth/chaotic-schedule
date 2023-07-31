<?php

namespace Skywarth\ChaoticSchedule;

use Illuminate\Console\Scheduling\Schedule;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;

class ChaoticSchedule
{

    private RandomNumberGeneratorAdapter $rng;

    public function __construct()
    {
        $this->rng=RNGFactory::getRngEngine();
    }

    public function registerMacros(){
        $this->registerRandomTimeScheduleMacro();
    }

    private function registerRandomTimeScheduleMacro(){
        Schedule::macro('randomTime', function () {
            $this->rng->intBetween(30,60);
            //do ya thing
            return $this;
        });
    }


}