<?php

namespace Skywarth\ChaoticSchedule;

use Illuminate\Console\Scheduling\Schedule;

class ChaoticSchedule
{

    public function __construct()
    {
    }

    public function registerMacros(){
        $this->registerRandomTimeScheduleMacro();
    }

    private function registerRandomTimeScheduleMacro(){
        Schedule::macro('randomTime', function () {
            //do ya thing
            return $this;
        });
    }

}