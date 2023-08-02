<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
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
        //TODO fix outer this access, maybe get rid of anonymous function
        $chaoticSchedule=$this;
        Event::macro('atRandom', function (string $minTime, string $maxTime) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomTimeSchedule($this,$minTime,$maxTime);

        });
    }

    public function randomTimeSchedule(Event $schedule,string $minTime, string $maxTime):Event{



        $this->getRng()->setSeed($this->getSeeder()->seedForDay('replace-me-with-command'));
        //H:i is 24 hour format
        $minTimeCasted=Carbon::createFromFormat('H:i',$minTime);
        $maxTimeCasted=Carbon::createFromFormat('H:i',$maxTime);
        //$maxTimeCasted=strtotime($maxTime);

        /*
       //cancelling below for now, I don't think it would provide homogenous distribution.
       $this->rng->intBetween($minTimeCasted,$maxTimeCasted);;
       */

        $minMinuteOfTheDay=($minTimeCasted->hour * 60) + $minTimeCasted->minute;
        $maxMinuteOfTheDay=($maxTimeCasted->hour * 60) + $maxTimeCasted->minute;


        $randomMOTD=$this->getRng()->intBetween($minMinuteOfTheDay,$maxMinuteOfTheDay);

        $designatedHour=$randomMOTD/60;
        $designatedMinute=$randomMOTD%60;


        $schedule->at("$designatedHour:$designatedMinute");

        return $schedule;
    }

    /**
     * @return RandomNumberGeneratorAdapter
     */
    protected function getRng(): RandomNumberGeneratorAdapter
    {
        //TODO: we shouldn't have this accessor, why would adapter be exposed ?
        return $this->rng;
    }

    /**
     * @return SeedGenerationService
     */
    protected function getSeeder(): SeedGenerationService
    {
        return $this->seeder;
    }







    private function randomTime(){

    }


}