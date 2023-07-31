<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;

class ChaoticSchedule
{

    private RandomNumberGeneratorAdapter $rng;
    private SeedGenerationService $seeder;

    public function __construct()
    {
        $this->rng=RNGFactory::getRngEngine();
    }

    public function registerMacros(){
        $this->registerRandomTimeScheduleMacro();
    }

/*$twister = new MersenneTwister(intval(date('Ymd')));

$hour = floor($twister->rangeint(600,1020) / 60);
$minute = $twister->rangeint(0,1440) % 60;*/

    private function registerRandomTimeScheduleMacro(){
        Schedule::macro('randomTime', function ($minTime,$maxTime) {

            //TODO: Move all the below to seeder!!
            //H:i is 24 hour format
            $minTimeCasted=Carbon::createFromFormat('H:i',$minTime);
            $maxTimeCasted=Carbon::createFromFormat('H:i',$maxTime);
            //$maxTimeCasted=strtotime($maxTime);

            /*
           //cancelling below for now, I don't think it would provide homogenous distribution.
           $this->rng->intBetween($minTimeCasted,$maxTimeCasted);;
           */

            $minMinuteOfTheDay=($minTimeCasted->hour * 60)+ $minTimeCasted->minute;
            $maxMinuteOfTheDay=($maxTimeCasted->hour * 60)+ $maxTimeCasted->minute;

            $randomMinute=$this->rng->intBetween($minMinuteOfTheDay,$maxMinuteOfTheDay);
            dd($randomMinute);

            //do ya thing
            return $this;
        });
    }


}