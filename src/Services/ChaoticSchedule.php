<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Skywarth\ChaoticSchedule\Enums\RandomDateScheduleBasis;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;

class ChaoticSchedule
{

    private RandomNumberGeneratorAdapter $rng;
    private SeedGenerationService $seeder;

    public function __construct(SeedGenerationService $seeder, RNGFactory $factory)
    {
        //$this->seeder=app()->make(SeedGenerationService::class);
        $this->seeder=$seeder;
        //$factory=new RNGFactory(config('chaotic-schedule.rng_engine.active_engine_slug'));
        //$factory=app()->make(RNGFactory::class,['slug'=>'mersenne-twister']);
        $this->rng=$factory->getRngEngine();
        //TODO: I'm still not convinced on this. Maybe there should be a facade that brings rng and seeder together.
    }


    /**
     * @throws IncompatibleClosureResponse
     */
    private function validateClosureResponse($closureResponse, $expected){
        $type=gettype($closureResponse);
        if($type!==$expected){
            throw new IncompatibleClosureResponse($expected,$type);
        }
    }


    /**
     * @throws IncorrectRangeException|InvalidDateFormatException
     * @throws IncompatibleClosureResponse
     */
    public function randomTimeSchedule(Event $schedule, string $minTime, string $maxTime, ?string $uniqueIdentifier=null,?callable $closure=null):Event{

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

        if(!empty($closure)){
            $randomMOTD=$closure($randomMOTD,$schedule);
            $this->validateClosureResponse($randomMOTD,'integer');
        }


        $designatedHour=($randomMOTD/60)%24;
        $designatedMinute=$randomMOTD%60;
        $schedule->at("$designatedHour:$designatedMinute");

        return $schedule;
    }

    /**
     * @throws IncorrectRangeException|IncompatibleClosureResponse
     */
    public function randomMinuteSchedule(Event $schedule, int $minMinutes=0, int $maxMinutes=59, ?string $uniqueIdentifier=null,?callable $closure=null):Event{

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


        if(!empty($closure)){
            $randomMinute=$closure($randomMinute,$schedule);
            $this->validateClosureResponse($randomMinute,'integer');
        }

        $randomMinute=$randomMinute%60;//Insurance. For now, it's completely for the closure.



        $schedule->hourlyAt($randomMinute);

        return $schedule;
    }




    public function randomDays(Event $schedule, int $periodType, ?array $daysOfTheWeek, int $timesMin, int $timesMax, ?string $uniqueIdentifier=null):Event{
        if(empty($daysOfTheWeek)){
            $daysOfTheWeek=[
                /*
                Schedule::MONDAY,
                Schedule::TUESDAY,
                Schedule::WEDNESDAY,
                Schedule::THURSDAY,
                Schedule::FRIDAY,
                Schedule::SATURDAY,
                Schedule::SUNDAY,
                */
                Carbon::MONDAY,
                Carbon::TUESDAY,
                Carbon::WEDNESDAY,
                Carbon::THURSDAY,
                Carbon::FRIDAY,
                Carbon::SATURDAY,
                Carbon::SUNDAY,
            ];
        }

        $identifier=$this->getScheduleIdentifier($schedule,$uniqueIdentifier);
        //TODO: validate times
        //TODO: validate daysOfTheWeek

        RandomDateScheduleBasis::validate($periodType);

        $seed=$this->getSeeder()->seedByDateScheduleBasis($identifier,$periodType);
        $this->getRng()->setSeed($seed);


        $randomTimes=$this->getRng()->intBetween($timesMin,$timesMax);

        //TODO: We need a handling for generating pRNG numbers in exact order. Something like ->next() or ->seek().
        //update: i think it does it automatically



        $periodBegin=Carbon::now()->startOf(RandomDateScheduleBasis::getString($periodType));
        $periodEnd=Carbon::now()->endOf(RandomDateScheduleBasis::getString($periodType));

        $period=CarbonPeriod::create($periodBegin, $periodEnd);

        /*foreach ($period as $index=>$date){
            $possibleDates->push($date);
        }*/
        $period=collect($period->toArray());
        //TODO: either do the filtering on the CarbonPeriod or the collection. Doing on the CarbonPeriod might be far efficient
        $possibleDates=$period->filter(function (Carbon $date) use($daysOfTheWeek){
            //filter based on designated $daysOfTheWeek and MAYBE closure
            return in_array($date->dayOfWeek,$daysOfTheWeek);
        });



        $designatedRuns=collect();
        for($i=0;$i<$randomTimes;$i++){
            $designatedRun=$possibleDates->random();//TODO: this breaks the pRNG contract, this is not pseudo random and certainly not bound to seed. Fix it.
            $designatedRuns->push($designatedRun);
        }

        /*
        if(!empty($closure)){
            $randomMOTD=$closure($randomMOTD,$schedule);
            $this->validateClosureResponse($randomMOTD,'integer');
        }*/

        //https://laravel.com/docs/10.x/scheduling#truth-test-constraints
        //"When using chained when methods, the scheduled command will only execute if all when conditions return true."
        //So this usage shouldn't stir other ->when() statements
        $schedule->when(function (Event $event) use($designatedRuns){
            $today=Carbon::now();
            return $designatedRuns->contains(function (Carbon $runDate) use($today){
               return $today->isSameDay($runDate);
            });
        });








        //WIP!!

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
        if(empty($uniqueIdentifier)){
            $exploded=explode(' ',$schedule->command);
            $uniqueIdentifier=$exploded[count($exploded)-1];
        }
        return $uniqueIdentifier;
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

        $this->registerRandomDaysRenameMeMacro();
    }

    private function registerAtRandomMacro(){
        $chaoticSchedule=$this;
        Event::macro('atRandom', function (string $minTime, string $maxTime,?string $uniqueIdentifier=null,?callable $closure=null) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomTimeSchedule($this,$minTime,$maxTime,$uniqueIdentifier,$closure);

        });
    }

    private function registerDailyAtRandomRandomMacro(){
        $chaoticSchedule=$this;
        Event::macro('dailyAtRandom', function (string $minTime, string $maxTime,?string $uniqueIdentifier=null,?callable $closure=null) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomTimeSchedule($this,$minTime,$maxTime,$uniqueIdentifier,$closure);

        });
    }

    private function registerHourlyAtRandomMacro(){
        $chaoticSchedule=$this;
        Event::macro('hourlyAtRandom', function (int $minMinutes=0, int $maxMinutes=59,?string $uniqueIdentifier=null,?callable $closure=null) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomMinuteSchedule($this,$minMinutes,$maxMinutes,$uniqueIdentifier,$closure);

        });
    }


    private function registerRandomDaysRenameMeMacro(){
        $chaoticSchedule=$this;
        Event::macro('randomDaysRenameMe', function (int $period, ?array $daysOfTheWeek,int $timesMin,int $timesMax,?string $uniqueIdentifier=null) use($chaoticSchedule){
            //Laravel automatically injects and replaces $this in the context

            return $chaoticSchedule->randomDays($this,$period,$daysOfTheWeek,$timesMin,$timesMax,$uniqueIdentifier);

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