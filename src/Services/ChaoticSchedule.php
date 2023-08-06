<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
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
        //TODO: I'm still not convinced on this. Maybe there should be a facade that brings rng and seeder together.
    }


    /**
     * @throws IncorrectRangeException|InvalidDateFormatException
     */
    public function randomTimeSchedule(Event $schedule, string $minTime, string $maxTime, ?string $uniqueIdentifier=null):Event{

        $identifier=$this->getScheduleIdentifier($schedule,$uniqueIdentifier);

        //dump($schedule->nextRunDate('now',0,true)->toDateTimeString());

        //H:i is 24 hour format
        try{
            $minTimeCasted=Carbon::createFromFormat('H:i',$minTime);
            $maxTimeCasted=Carbon::createFromFormat('H:i',$maxTime);
        }catch (InvalidFormatException $ex){
            throw new InvalidDateFormatException("Given time format is invalid. minTime and maxTime parameters should be in 'H:i' format.",0,$ex);
        }
        if($minTimeCasted->isAfter($maxTimeCasted)){
            throw new IncorrectRangeException($minTime,$maxTime);
        }

        $minMinuteOfTheDay=($minTimeCasted->hour * 60) + $minTimeCasted->minute;
        $maxMinuteOfTheDay=($maxTimeCasted->hour * 60) + $maxTimeCasted->minute;

        $randomMOTD=$this->getRng()
            ->setSeed($this->getSeeder()->seedForDay($identifier))
            ->intBetween($minMinuteOfTheDay,$maxMinuteOfTheDay);


        $designatedHour=$randomMOTD/60;
        $designatedMinute=$randomMOTD%60;


        $schedule->at("$designatedHour:$designatedMinute");

        return $schedule;
    }

    /**
     * @throws IncorrectRangeException
     */
    public function randomMinuteSchedule(Event $schedule, int $minMinutes=0, int $maxMinutes=59, ?string $uniqueIdentifier=null):Event{

        if($minMinutes>$maxMinutes){
            throw new IncorrectRangeException($minMinutes,$maxMinutes);
        }
        if($minMinutes<0 || $maxMinutes>59){
            throw new \OutOfRangeException('Provide min-max minute parameters between 0 and 59.');
        }

        $identifier=$this->getScheduleIdentifier($schedule,$uniqueIdentifier);

        //H:i is 24 hour format


        $randomMinute=$this->getRng()
            ->setSeed($this->getSeeder()->seedForDay($identifier))
            ->intBetween($minMinutes,$maxMinutes);



        $schedule->hourlyAt($randomMinute);

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



    protected function getScheduleIdentifier(Event $schedule,?string $uniqueIdentifier=null):string{
        return $uniqueIdentifier??($schedule->command);
    }




    /* //MACROS BEGIN

       __  __    _    ____ ____   ___  ____    ____  _____ ____ ___ _   _
      |  \/  |  / \  / ___|  _ \ / _ \/ ___|  | __ )| ____/ ___|_ _| \ | |
      | |\/| | / _ \| |   | |_) | | | \___ \  |  _ \|  _|| |  _ | ||  \| |
      | |  | |/ ___ \ |___|  _ <| |_| |___) | | |_) | |__| |_| || || |\  |
      |_|  |_/_/   \_\____|_| \_\\___/|____/  |____/|_____\____|___|_| \_|


    */
    //TODO: maybe move this section to service provider
    public function registerMacros(){
        $this->registerAtRandomMacro();
        $this->registerHourlyAtRandomMacro();
        $this->registerDailyAtRandomRandomMacro();
    }

    private function registerAtRandomMacro(){
        $chaoticSchedule=$this;
        Event::macro('atRandom', function (string $minTime, string $maxTime,?string $uniqueIdentifier=null) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomTimeSchedule($this,$minTime,$maxTime,$uniqueIdentifier);

        });
    }

    private function registerDailyAtRandomRandomMacro(){
        $chaoticSchedule=$this;
        Event::macro('dailyAtRandom', function (string $minTime, string $maxTime,?string $uniqueIdentifier=null) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomTimeSchedule($this,$minTime,$maxTime,$uniqueIdentifier);

        });
    }

    private function registerHourlyAtRandomMacro(){
        $chaoticSchedule=$this;
        Event::macro('hourlyAtRandom', function (int $minMinutes=0, int $maxMinutes=59,?string $uniqueIdentifier=null) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomMinuteSchedule($this,$minMinutes,$maxMinutes,$uniqueIdentifier);

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