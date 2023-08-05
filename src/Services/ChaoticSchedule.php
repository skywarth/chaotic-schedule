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
        $this->seeder=app()->make(SeedGenerationService::class);
        $this->rng=RNGFactory::getRngEngine();
    }


    public function randomTimeSchedule(Event $schedule,string $minTime, string $maxTime,?string $uniqueIdentifier=null):Event{

        $uniqueIdentifier=$uniqueIdentifier??$this->getScheduleIdentifier($schedule);

        //H:i is 24 hour format
        $minTimeCasted=Carbon::createFromFormat('H:i',$minTime);
        $maxTimeCasted=Carbon::createFromFormat('H:i',$maxTime);

        $minMinuteOfTheDay=($minTimeCasted->hour * 60) + $minTimeCasted->minute;
        $maxMinuteOfTheDay=($maxTimeCasted->hour * 60) + $maxTimeCasted->minute;

        $randomMOTD=$this->getRng()
            ->setSeed($this->getSeeder()->seedForDay($uniqueIdentifier))
            ->intBetween($minMinuteOfTheDay,$maxMinuteOfTheDay);


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
        return $this->rng;
    }

    /**
     * @return SeedGenerationService
     */
    protected function getSeeder(): SeedGenerationService
    {
        return $this->seeder;
    }



    protected function getScheduleIdentifier(Event $schedule):string{
        return $schedule->command;
    }




    /* //MACROS BEGIN

       __  __    _    ____ ____   ___  ____    ____  _____ ____ ___ _   _
      |  \/  |  / \  / ___|  _ \ / _ \/ ___|  | __ )| ____/ ___|_ _| \ | |
      | |\/| | / _ \| |   | |_) | | | \___ \  |  _ \|  _|| |  _ | ||  \| |
      | |  | |/ ___ \ |___|  _ <| |_| |___) | | |_) | |__| |_| || || |\  |
      |_|  |_/_/   \_\____|_| \_\\___/|____/  |____/|_____\____|___|_| \_|


    */

    public function registerMacros(){
        $this->registerRandomTimeScheduleMacro();
    }

    private function registerRandomTimeScheduleMacro(){
        $chaoticSchedule=$this;
        Event::macro('atRandom', function (string $minTime, string $maxTime,?string $uniqueIdentifier=null) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomTimeSchedule($this,$minTime,$maxTime,$uniqueIdentifier);

        });
    }
    /* //MACROS END
     __  __          _____ _____   ____   _____   ______ _   _ _____
    |  \/  |   /\   / ____|  __ \ / __ \ / ____| |  ____| \ | |  __ \
    | \  / |  /  \ | |    | |__) | |  | | (___   | |__  |  \| | |  | |
    | |\/| | / /\ \| |    |  _  /| |  | |\___ \  |  __| | . ` | |  | |
    | |  | |/ ____ \ |____| | \ \| |__| |____) | | |____| |\  | |__| |
    |_|  |_/_/    \_\_____|_|  \_\\____/|_____/  |______|_| \_|_____/

    */


}