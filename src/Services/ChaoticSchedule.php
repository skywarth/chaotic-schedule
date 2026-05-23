<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Console\Scheduling\Event;
use InvalidArgumentException;
use LogicException;
use OutOfRangeException;
use Skywarth\ChaoticSchedule\Enums\RandomDateScheduleBasis;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\Exceptions\RunTimesExpectationCannotBeMet;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;

class ChaoticSchedule
{

    public const ALL_DOW = [
        Carbon::MONDAY,
        Carbon::TUESDAY,
        Carbon::WEDNESDAY,
        Carbon::THURSDAY,
        Carbon::FRIDAY,
        Carbon::SATURDAY,
        Carbon::SUNDAY,
    ];

    private readonly RandomNumberGeneratorAdapter $rng;

    /**
     * Usually these parameters are resolved via dependency injection from Laravel Service Container
     */
    public function __construct(
        private readonly SeedGenerationService $seeder,
        RNGFactory $factory,
    ) {
        $this->rng = $factory->getRngEngine();
    }


    /**
     * @return Carbon
     */
    public function getBasisDate():Carbon{
        return $this->seeder->getBasisDate();//maybe add clone as well ?
    }


    /**
     * @throws IncompatibleClosureResponse
     */
    private function assertIntegerClosureResponse(mixed $closureResponse): void
    {
        if (!is_int($closureResponse)) {
            throw new IncompatibleClosureResponse('integer', gettype($closureResponse));
        }
    }

    /**
     * @throws IncompatibleClosureResponse
     */
    private function assertObjectClosureResponse(mixed $closureResponse): void
    {
        if (!is_object($closureResponse)) {
            throw new IncompatibleClosureResponse('object', gettype($closureResponse));
        }
    }

    /**
     * Validates that minute-of-the-hour bounds are within [0,59] and ordered.
     *
     * @throws IncorrectRangeException
     */
    private function assertMinuteRange(int $minMinutes, int $maxMinutes): void
    {
        if ($minMinutes > $maxMinutes) {
            throw new IncorrectRangeException((string) $minMinutes, (string) $maxMinutes);
        }
        if ($minMinutes < 0 || $maxMinutes > 59) {
            throw new OutOfRangeException('Provide min-max minute parameters between 0 and 59.');
        }
    }

    /**
     * Validates that the run-times bounds are non-negative and ordered.
     *
     * @throws IncorrectRangeException
     */
    private function assertTimesRange(int $timesMin, int $timesMax): void
    {
        if ($timesMin < 0 || $timesMax < 0) {
            throw new LogicException('TimesMin and TimesMax has to be non-negative numbers!');
        }
        if ($timesMin > $timesMax) {
            throw new IncorrectRangeException((string) $timesMin, (string) $timesMax);
        }
    }

    /**
     * @param Event $schedule
     * @param CarbonInterface $date Designated date to run the Schedule/Event instance at
     * @return Event
     */
    private function scheduleToDate(Event $schedule, CarbonInterface $date):Event{
        $day = $date->day;
        $month = $date->month;

        //Below enables to indicate nextRunDate
        //Hopefully it also enables testing whether it'll run at given date or not
        //The fact that we can't schedule including the year is terrible. Makes testing and planning miserable.
        return $schedule->cron("* * $day $month *");//Laravel cron doesn't allow year, sad :'(

    }


    /**
     * @throws IncorrectRangeException|InvalidDateFormatException
     * @throws IncompatibleClosureResponse
     */
    public function randomTimeSchedule(Event $schedule, string $minTime, string $maxTime, ?string $uniqueIdentifier=null,?callable $closure=null):Event{

        $identifier=$this->getScheduleIdentifier($schedule,$uniqueIdentifier);

        //H:i is 24 hour format
        try{
            $minTimeCasted=Carbon::createFromFormat('H:i',$minTime);
            $maxTimeCasted=Carbon::createFromFormat('H:i',$maxTime);
        }catch (Exception $ex){
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
            $this->assertIntegerClosureResponse($randomMOTD);
        }


        $designatedHour=intdiv($randomMOTD, 60)%24;
        $designatedMinute=$randomMOTD%60;
        $schedule->at("$designatedHour:$designatedMinute");

        return $schedule;
    }

    /**
     * @throws IncorrectRangeException|IncompatibleClosureResponse
     */
    public function randomMinuteSchedule(Event $schedule, int $minMinutes=0, int $maxMinutes=59, ?string $uniqueIdentifier=null,?callable $closure=null):Event{

        $this->assertMinuteRange($minMinutes, $maxMinutes);

        $identifier=$this->getScheduleIdentifier($schedule,$uniqueIdentifier);

        //H:i is 24 hour format


        $randomMinute=$this->getRng()
            ->setSeed($this->getSeeder()->seedForHour($identifier))
            ->intBetween($minMinutes,$maxMinutes);



        //TODO: test the closure as well
        if(!empty($closure)){
            $randomMinute=$closure($randomMinute,$schedule);
            $this->assertIntegerClosureResponse($randomMinute);
        }

        $randomMinute=$randomMinute%60;//Insurance. For now, it's completely for the closure.



        $schedule->hourlyAt($randomMinute);

        return $schedule;
    }

    /**
     * @param Event $schedule
     * @param int $minMinutes
     * @param int $maxMinutes
     * @param int $timesMin
     * @param int $timesMax
     * @param string|null $uniqueIdentifier
     * @param callable|null $closure
     * @return Event
     * @throws IncompatibleClosureResponse
     * @throws IncorrectRangeException
     * @throws RunTimesExpectationCannotBeMet
     */
    public function randomMultipleMinutesSchedule(Event $schedule, int $minMinutes=0, int $maxMinutes=59, int $timesMin=1, int $timesMax=1, ?string $uniqueIdentifier=null, ?callable $closure=null):Event{

        //TODO: merging this method and randomMinute() kinda makes sense, not sure if I should. Open to discussion.

        $this->assertMinuteRange($minMinutes, $maxMinutes);
        $this->assertTimesRange($timesMin, $timesMax);



        $identifier=$this->getScheduleIdentifier($schedule,$uniqueIdentifier);

        //H:i is 24 hour format

        $this->getRng()->setSeed($this->getSeeder()->seedForHour($identifier));//TODO: maybe optional parameter for seed period? E.g: I want seed to be exact and same for a week

        $runTimes=$this->getRng()->intBetween($timesMin,$timesMax);
        $designatedRunMinutes=collect();
        $possibleMinutes=collect(range($minMinutes,$maxMinutes));
        if($possibleMinutes->count()<$timesMax){
            $possibleMinutesCount=$possibleMinutes->count();
            throw new RunTimesExpectationCannotBeMet("For '$identifier' command, maximum of '$timesMax' was desired however this could not be satisfied since there isn't that many minutes (only $possibleMinutesCount minutes available) for the given period and constraints. Please check your closure, times min-max and minimum/maximum minutes.");
        }
        for($i=0;$i<$runTimes;$i++){
            $randomIndex=$this->getRng()->intBetween(0,$possibleMinutes->count()-1);
            $designatedRunMinute=$possibleMinutes->pull($randomIndex);
            $possibleMinutes=$possibleMinutes->values();
            $designatedRunMinutes->push($designatedRunMinute);
        }

        if(!empty($closure)){
            $designatedRunMinutes=$closure($designatedRunMinutes,$schedule);
            $this->assertObjectClosureResponse($designatedRunMinutes);//Collection of minute of the hour (e.g: 23, 57, 7) expected
        }

        $designatedRunMinutes=$designatedRunMinutes->values();

        //Filtering out past minutes, leaving only the future minutes
        $designatedRunMinutes=$designatedRunMinutes->filter(function ($minute){
            return $this->getBasisDate()->minute<=$minute;
        });

        $designatedNextRunMinute=null;
        if($designatedRunMinutes->isNotEmpty()){
            $schedule->when(function() use($designatedRunMinutes){
                return $designatedRunMinutes->contains($this->getBasisDate()->minute);
            });//FIXED bug here. This can easily conflict because closure is applied afterwards. I think we should go with passing array to closure

            $randomMinute=$designatedRunMinutes->sort()->first();


            $designatedNextRunMinute=$randomMinute%60;//Insurance. For now, it's completely for the closure.

        }else{
            // This section means there is no designatedRun minute available.
            // So we need to prevent the command from running via returning falsy when() statement and some future bogus date
            $schedule->when(false);
            $designatedNextRunMinute=($this->getBasisDate()->minute-2)%60;//Bogus minute
        }


        //$schedule->hourlyAt($designatedNextRunMinute);//BUG here, hourlyAt conflicts with everySixHours, everyTwoHours etc.
        //hourlyAt also makes testing difficult
        $schedule->at($this->getBasisDate()->hour.':'.$designatedNextRunMinute);



        return $schedule;
    }


    /**
     * Normalize and validate the days-of-the-week filter for randomDaysSchedule.
     *
     * @param int[]|null $daysOfTheWeek
     * @return int[]
     * @throws InvalidArgumentException
     */
    private function normalizeDaysOfTheWeek(?array $daysOfTheWeek): array
    {
        if (empty($daysOfTheWeek)) {
            return self::ALL_DOW;
        }
        foreach ($daysOfTheWeek as $dayNum) {
            // @phpstan-ignore notIdentical.alwaysFalse
            if (gettype($dayNum) !== 'integer') {
                throw new InvalidArgumentException('daysOfTheWeek contains non-integer value! It should contain only integer values which represent days of the week.');
            }
            if (!in_array($dayNum, self::ALL_DOW)) {
                throw new InvalidArgumentException("The number=$dayNum doesn't correspond to a day of the week number (Carbon).");
            }
        }
        return $daysOfTheWeek;
    }

    /**
     * @param int[] $daysOfTheWeek
     * @throws IncorrectRangeException
     * @throws IncompatibleClosureResponse|RunTimesExpectationCannotBeMet
     */
    public function randomDaysSchedule(Event $schedule, RandomDateScheduleBasis $periodType, ?array $daysOfTheWeek, int $timesMin, int $timesMax, ?string $uniqueIdentifier=null, ?callable $closure=null):Event{
        $daysOfTheWeek = $this->normalizeDaysOfTheWeek($daysOfTheWeek);

        //Validations...
        $identifier=$this->getScheduleIdentifier($schedule,$uniqueIdentifier);
        $this->assertTimesRange($timesMin, $timesMax);

        $seed=$this->getSeeder()->seedByDateScheduleBasis($identifier,$periodType);
        $this->getRng()->setSeed($seed);


        $randomTimes=$this->getRng()->intBetween($timesMin,$timesMax);

        //TODO: We need a handling for generating pRNG numbers in exact order. Something like ->next() or ->seek().
        //update: i think it does it automatically



        $periodBegin=$this->getBasisDate()->startOf($periodType->periodString());
        $periodEnd=$this->getBasisDate()->endOf($periodType->periodString());

        $period=CarbonPeriod::create($periodBegin, $periodEnd);


        $period=collect($period->toArray());
        //TODO: either do the filtering on the CarbonPeriod or the collection. Doing on the CarbonPeriod might be far efficient
        $possibleDates=$period->filter(function (CarbonInterface $date) use($daysOfTheWeek){
            //filter based on designated $daysOfTheWeek and MAYBE closure
            return in_array($date->dayOfWeek,$daysOfTheWeek);
        });//values() is for reindexing, so the keys are sure to be consecutive integers




        if(!empty($closure)){
            $possibleDates=$closure($possibleDates,$schedule);//I'm still not sure whether we should pass $possibleDates or $designatedRuns to the closure.
            $this->assertObjectClosureResponse($possibleDates);//Collection of dates expected
        }

        $possibleDates=$possibleDates->values();


        if($possibleDates->count()<$timesMax){
            $possibleDateCount=$possibleDates->count();
            throw new RunTimesExpectationCannotBeMet("For '$identifier' command, maximum of '$timesMax' was desired however this could not be satisfied since there isn't that many (only $possibleDateCount available) for the given period and constraints. Please check your closure, day of the week and period parameters.");
        }





        $picked = [];
        for($i=0;$i<$randomTimes;$i++){
            $randomIndex=$this->getRng()->intBetween(0,$possibleDates->count()-1);
            $picked[] = $possibleDates->pull($randomIndex);
            $possibleDates=$possibleDates->values();//re-indexing
        }
        $designatedRuns = collect($picked);



        //Filtering out past dates, leaving only future runs
        $designatedRuns=$designatedRuns->filter(function (CarbonInterface $date){
            $dateOnly=$date->startOfDay();
            return $dateOnly->isAfter($this->getBasisDate()->startOfDay()) || $dateOnly->isSameDay($this->getBasisDate()->startOfDay()); //TODO: simplify
        });


        //https://laravel.com/docs/10.x/scheduling#truth-test-constraints
        //"When using chained when methods, the scheduled command will only execute if all when conditions return true."
        //So this usage shouldn't stir other ->when() statements
        if($designatedRuns->isNotEmpty()){
            $schedule->when(function() use($designatedRuns){
                return $designatedRuns->contains(function (CarbonInterface $runDate){
                    return $this->getBasisDate()->isSameDay($runDate);
                });
            });


            $closestDesignatedRun=$designatedRuns->sortBy(function (CarbonInterface $date){
                //return $date->diffInDays($this->getBasisDate());
                //Keeping that commented tedious bug as trophy. Costed me several hours. Suck it!
                //Carbon 3: diffInDays returns signed float; abs() restores Carbon 2's absolute semantic.
                return abs($date->startOfDay()->diffInDays($this->getBasisDate()->startOfDay()));
            })->first();


            $schedule=$this->scheduleToDate($schedule,$closestDesignatedRun);
        }else{
            // This section means there is no designatedRun date available.
            // So we need to prevent the command from running via returning falsy when() statement and some future bogus date
            $schedule->when(false);

            $bogusDate=$periodEnd->clone()->next($periodType->periodString());//Maybe instead of this, just offset to next  year.

            if($periodType===RandomDateScheduleBasis::YEAR){
                $bogusDate->addDay();//otherwise it'll reschedule for today next year, and since we can't indicate year on laravel CRON, it resolves as to running today.
                //since it's bogus date, we can alter it as we please. As long as it prevents the command from running
            }

            $schedule=$this->scheduleToDate($schedule,$bogusDate);
        }

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


    /**
     * @param Event $schedule
     * @param string|null $uniqueIdentifier
     * @return string
     */
    protected function getScheduleIdentifier(Event $schedule, ?string $uniqueIdentifier=null):string{
        if(empty($uniqueIdentifier)){
            $exploded=explode(' ',$schedule->command);
            $uniqueIdentifier=$exploded[count($exploded)-1];
        }
        return $uniqueIdentifier;
    }




    // === MACROS ===
    /*
     * __  __    _    ____ ____   ___  ____    ____  _____ ____ ___ _   _
      |  \/  |  / \  / ___|  _ \ / _ \/ ___|  | __ )| ____/ ___|_ _| \ | |
      | |\/| | / _ \| |   | |_) | | | \___ \  |  _ \|  _|| |  _ | ||  \| |
      | |  | |/ ___ \ |___|  _ <| |_| |___) | | |_) | |__| |_| || || |\  |
      |_|  |_/_/   \_\____|_| \_\\___/|____/  |____/|_____\____|___|_| \_|
     */

    public function registerMacros(): void
    {
        $this->registerAtRandomMacro();
        $this->registerHourlyAtRandomMacro();
        $this->registerHourlyMultipleAtRandomMacro();
        $this->registerDailyAtRandomRandomMacro();

        $this->registerRandomDaysMacro();
    }

    private function registerAtRandomMacro(): void
    {
        $chaoticSchedule = $this;
        Event::macro('atRandom', function (string $minTime, string $maxTime, ?string $uniqueIdentifier = null, ?callable $closure = null) use ($chaoticSchedule) {
            /** @var Event $this */
            return $chaoticSchedule->randomTimeSchedule($this, $minTime, $maxTime, $uniqueIdentifier, $closure);
        });
    }

    private function registerDailyAtRandomRandomMacro(): void
    {
        $chaoticSchedule = $this;
        Event::macro('dailyAtRandom', function (string $minTime, string $maxTime, ?string $uniqueIdentifier = null, ?callable $closure = null) use ($chaoticSchedule) {
            /** @var Event $this */
            return $chaoticSchedule->randomTimeSchedule($this, $minTime, $maxTime, $uniqueIdentifier, $closure);
        });
    }

    private function registerHourlyAtRandomMacro(): void
    {
        $chaoticSchedule = $this;
        Event::macro('hourlyAtRandom', function (int $minMinutes = 0, int $maxMinutes = 59, ?string $uniqueIdentifier = null, ?callable $closure = null) use ($chaoticSchedule) {
            /** @var Event $this */
            return $chaoticSchedule->randomMinuteSchedule($this, $minMinutes, $maxMinutes, $uniqueIdentifier, $closure);
        });
    }

    private function registerHourlyMultipleAtRandomMacro(): void
    {
        $chaoticSchedule = $this;
        Event::macro('hourlyMultipleAtRandom', function (int $minMinutes = 0, int $maxMinutes = 59, int $timesMin = 1, int $timesMax = 1, ?string $uniqueIdentifier = null, ?callable $closure = null) use ($chaoticSchedule) {
            /** @var Event $this */
            return $chaoticSchedule->randomMultipleMinutesSchedule($this, $minMinutes, $maxMinutes, $timesMin, $timesMax, $uniqueIdentifier, $closure);
        });
    }


    private function registerRandomDaysMacro(): void
    {
        $chaoticSchedule = $this;
        Event::macro('randomDays', function (RandomDateScheduleBasis $periodType, ?array $daysOfTheWeek, int $timesMin, int $timesMax, ?string $uniqueIdentifier = null, ?callable $closure = null) use ($chaoticSchedule) {
            /** @var Event $this */
            return $chaoticSchedule->randomDaysSchedule($this, $periodType, $daysOfTheWeek, $timesMin, $timesMax, $uniqueIdentifier, $closure);
        });
    }

    /*
     __  __          _____ _____   ____   _____   ______ _   _ _____
    |  \/  |   /\   / ____|  __ \ / __ \ / ____| |  ____| \ | |  __ \
    | \  / |  /  \ | |    | |__) | |  | | (___   | |__  |  \| | |  | |
    | |\/| | / /\ \| |    |  _  /| |  | |\___ \  |  __| | . ` | |  | |
    | |  | |/ ____ \ |____| | \ \| |__| |____) | | |____| |\  | |__| |
    |_|  |_/_/    \_\_____|_|  \_\\____/|_____/  |______|_| \_|_____/

     */
}
