<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Skywarth\ChaoticSchedule\Enums\RandomDateScheduleBasis;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\RNGs\Adapters\MersenneTwisterAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\SeedSpringAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class RandomTimeMacrosTest extends AbstractChaoticScheduleTest
{


    public function testRandomTimeIncorrectParameterFormatException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(InvalidDateFormatException::class);
        $this->getChaoticSchedule()->randomTimeSchedule($schedule,'1000','12:00');

    }

    public function testRandomTimeClosureParamEvenNumbersOnly()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $runDates=collect();
        for ($i=0;$i<25;$i++){
            $date=$this->getChaoticSchedule()->randomTimeSchedule($schedule,'09:06','09:44','test'.$i,function (int $motd){
                //return $motd;//ensures that it only schedules for odd-number minutes
                return $motd-(($motd%2)-1);//ensures that it only schedules/runs on only odd-number minutes
            })->nextRunDate();
            $runDates->push($date->minute);
        }

        $evenMinuteRuns=$runDates->filter(function($runMin){
           return $runMin%2==0;
        });

        $this->assertEquals(0,$evenMinuteRuns->count());

    }

    public function testRandomTimeClosureThrowsIncompatibleResponse()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncompatibleClosureResponse::class);
        $this->getChaoticSchedule()->randomTimeSchedule($schedule,'09:00','15:00','test2',function (int $motd){
            return [];
        });
    }

    public function testRandomTimeIncorrectParameterOrderException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->getChaoticSchedule()->randomTimeSchedule($schedule,'18:00','09:00');
    }

    public function testRandomMinuteIncorrectParameterFormatException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(\OutOfRangeException::class);
        $this->getChaoticSchedule()->randomMinuteSchedule($schedule,-55,44);
    }
    public function testRandomMinuteIncorrectParameterOrderException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->getChaoticSchedule()->randomMinuteSchedule($schedule,44,13);
    }

    public function testRandomTimeSameCommandNoIdentifierConsistency()
    {
        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->wednesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->wednesdays();
        $comm1NextRun=$this->getChaoticSchedule()->randomTimeSchedule($comm1,'09:00','13:00')->nextRunDate();
        $comm2NextRun=$this->getChaoticSchedule()->randomTimeSchedule($comm2,'09:00','13:00')->nextRunDate();
        $datesEqual=$comm1NextRun->eq($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomTimeSameCommandCustomIdentifierDifference()
    {
        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->wednesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->wednesdays();
        $comm1NextRun=$this->getChaoticSchedule()->randomTimeSchedule($comm1,'09:00','13:00')->nextRunDate();
        $comm2NextRun=$this->getChaoticSchedule()->randomTimeSchedule($comm2,'09:00','13:00','testing')->nextRunDate();
        $datesEqual=$comm1NextRun->notEqualTo($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomTimeDifferentCommandNoIdentifierDifference()
    {
        $schedule1 = new Schedule();
        $comm1=$schedule1->command('foo')->wednesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('bar')->wednesdays();
        $comm1NextRun=$this->getChaoticSchedule()->randomTimeSchedule($comm1,'09:00','13:00')->nextRunDate();
        $comm2NextRun=$this->getChaoticSchedule()->randomTimeSchedule($comm2,'09:00','13:00')->nextRunDate();
        $datesEqual=$comm1NextRun->notEqualTo($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomTimeDifferentCommandSameCustomIdentifierConsistency()
    {
        $schedule1 = new Schedule();
        $comm1=$schedule1->command('foo')->wednesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('bar')->wednesdays();
        $comm1NextRun=$this->getChaoticSchedule()->randomTimeSchedule($comm1,'09:00','13:00','testing')->nextRunDate();
        $comm2NextRun=$this->getChaoticSchedule()->randomTimeSchedule($comm2,'09:00','13:00','testing')->nextRunDate();
        $datesEqual=$comm1NextRun->eq($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomTimeConsistencyThroughoutTheDay()
    {

        //$chaoticSchedule=$this->getChaoticSchedule();

        $designatedRuns=collect();

        $date=Carbon::now()->startOfDay();
        $schedule = new Schedule();
        $schedule=$schedule->command('foo')->daily();
        for($i=0;$i<100;$i++){
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($date),
                new RNGFactory('mersenne-twister')
            );
            $nextRun=$chaoticSchedule->randomTimeSchedule($schedule,'09:00','21:00')->nextRunDate();
            $designatedRuns->push($nextRun->format('Y-m-d H:i'));
            $date->addMinutes(10);
        }
        $uniqueRunTimes=$designatedRuns->unique();

        $this->assertEquals(1,$uniqueRunTimes->count());
        $this->assertSame($designatedRuns->toArray()[0],$uniqueRunTimes[0]);
    }


    public function testRandomTimeWithinLimits()
    {

        $min=Carbon::createFromFormat('H:i','15:28');
        $max=Carbon::createFromFormat('H:i','16:32');

        $schedules=$this->generateRandomTimeConsecutiveDays(
            100,
            self::DEFAULT_RNG_ENGINE_SLUG,
            $min->format('H:i'),
            $max->format('H:i')
        );
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate();
        });


        $designatedRuns=$designatedRuns->filter(function (Carbon $carbon) use($min,$max){
            return !($carbon->clone()->setDate($min->year,$min->month,$min->day)->between($min,$max));
        });

        $this->assertEquals(0,$designatedRuns->count());


    }

    public function testRandomTimeDistributionHomogeneityChiSquared()
    {

        $schedules=$this->generateRandomTimeConsecutiveDays(80,self::DEFAULT_RNG_ENGINE_SLUG,'06:18','19:42',Carbon::createFromDate(2017,10,12));//increase maybe
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate();
        });
        $designatedRunTimes=$designatedRuns->map(function (Carbon $date){
            return $date->format('H:i');
        });
        $intervals=24*60;
        $observed=collect()->pad($intervals, 0);


        $designatedRunTimes->each(function ($time) use ($observed) {
            $parts = explode(':', $time);
            $hour = (int) $parts[0];
            $minute = (int) $parts[1];
            $index = $hour * 60 + $minute;
            $observed->put($index, $observed[$index] + 1);
        });

        $expectedCount = $designatedRunTimes->count() / $intervals;
        $expected = collect()->pad($intervals, $expectedCount);
        $chiSquareStat = $this->calcChiSquared($observed, $expected);

        $chiSquareCriticalValue = 1470; // This value should be checked. Old one was 1439
        $this->assertLessThan($chiSquareCriticalValue,$chiSquareStat);
    }


    public function testRandomTimeDistributionHomogeneityByEntropy()
    {
        $thresholdPercentage=90;//percentage based, between 0 and 100. maybe 95%
        $samplingSize=100;


        $schedules=$this->generateRandomTimeConsecutiveDays($samplingSize);
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate();
        });
        $designatedRunTimes=$designatedRuns->map(function (Carbon $date){
            return $date->format('H:i');
        });

        // Convert times to minute intervals from the start of the day
        $minuteIntervals = $designatedRunTimes->map(function ($time) {
            [$hours, $minutes] = explode(':', $time);
            return ($hours * 60) + $minutes;
        });

        // Count the occurrences of each time interval
        $counts = $minuteIntervals->countBy()->all();


        // Calculate the probabilities for each time interval
        $probabilities = collect($counts)->map(function ($count) use ($designatedRunTimes) {
            return $count / $designatedRunTimes->count();
        });

        // Calculate entropy
        $entropy = -1 * $probabilities->sum(function ($probability) {
                return $probability * log($probability, 2);
            });

        // The maximum entropy for a uniform distribution over 1440 minutes is log2(1440)
        $maxEntropy = log($samplingSize, 2);



        // Let's assume an arbitrary threshold, say 95%. You can adjust this based on your needs.
        $threshold = ($thresholdPercentage/100) * $maxEntropy;

        // Assertion: The calculated entropy should be above 95% of the maximum possible entropy for a uniform distribution
        $this->assertGreaterThanOrEqual($threshold, $entropy);
    }




    public function testRandomMinuteClosureThrowsIncompatibleResponse()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncompatibleClosureResponse::class);
        $this->getChaoticSchedule()->randomMinuteSchedule($schedule,2,11,null,function (int $minute){
            return [];
        });
    }


    public function testRandomMinuteSameCommandNoIdentifierConsistency()
    {
        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->tuesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->tuesdays();
        $comm1NextRun=$this->getChaoticSchedule()->randomMinuteSchedule($comm1,10,55)->nextRunDate();
        $comm2NextRun=$this->getChaoticSchedule()->randomMinuteSchedule($comm2,10,55)->nextRunDate();
        $datesEqual=$comm1NextRun->eq($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomMinuteSameCommandCustomIdentifierDifference()
    {
        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->weekdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->weekdays();
        $comm1NextRun=$this->getChaoticSchedule()->randomMinuteSchedule($comm1,1,59)->nextRunDate();
        $comm2NextRun=$this->getChaoticSchedule()->randomMinuteSchedule($comm2,1,59,'get-customized-lmao')->nextRunDate();
        $datesEqual=$comm1NextRun->notEqualTo($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomMinuteWithinLimits()
    {

        $min=23;
        $max=42;

        $schedules=$this->generateRandomMinuteConsecutiveHours(
            100,
            self::DEFAULT_RNG_ENGINE_SLUG,
            $min,
            $max,
            Carbon::createFromDate(2023,9,10)
        );
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate();
        });


        $designatedRuns=$designatedRuns->filter(function (Carbon $carbon) use($min,$max){
            $minute=$carbon->minute;
            return !($minute>=$min && $minute<=$max);
        });

        $this->assertEquals(0,$designatedRuns->count());
    }


    public function testRandomMinuteConsistencyThroughoutTheHour()
    {
        $schedules=$this->generateRandomMinuteConsecutiveMinutes(60,1,59,Carbon::createFromDate(2023,7,12),MersenneTwisterAdapter::getAdapterSlug());
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate()->minute;
        });

        $uniqueRunMinutes=$designatedRuns->unique();

        $this->assertEquals(1,$uniqueRunMinutes->count());


    }

    public function testRandomMinuteInConsistencyThroughoutTheDay()
    {
        $schedules=$this->generateRandomMinuteConsecutiveMinutes(1440,1,59,Carbon::createFromDate(2012,4,13),SeedSpringAdapter::getAdapterSlug());
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate()->minute;
        });

        $uniqueRunMinutes=$designatedRuns->unique();

        $this->assertNotEquals(1,$uniqueRunMinutes->count());


    }


    protected function randomMultipleMinuteTestingBoilerplate(Carbon $nowMock,string $rngEngineSlug,int $minutesMin, int $minutesMax, int $timesMin, int $timesMax):Collection{
        $runMinutes=collect();

        for($i=0;$i<=59;$i++){
            $schedule = new Schedule();
            $command=$schedule->command('test');
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($nowMock),
                new RNGFactory($rngEngineSlug)
            );

            Carbon::setTestNow($nowMock); //Mock carbon now for Laravel event
            $schedule=$chaoticSchedule->randomMultipleMinutesSchedule($command,$minutesMin,$minutesMax,$timesMin,$timesMax);

            if($schedule->isDue(app())){
                $runMinutes->push($nowMock->minute);
            }
            $nowMock->addminute();
            Carbon::setTestNow();
        }
        return $runMinutes->unique();
    }

    public function testRandomMultipleMinuteVariableTimesBetweenLimits()
    {
        $nowMock=Carbon::createFromDate(2022,07,16)->setTime(15,0);
        $rngEngineSlug='mersenne-twister';
        $minutesMin=17;
        $minutesMax=56;
        $timesMin=4;
        $timesMax=8;

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$timesMin,$timesMax);

        $this->assertLessThanOrEqual($timesMax,$runMinutes->count());
        $this->assertGreaterThanOrEqual($timesMin,$runMinutes->count());


    }

    public function testRandomMultipleMinuteExactTimes()
    {
        $nowMock=Carbon::createFromDate(2048,9,12)->setTime(07,0);
        $rngEngineSlug='mersenne-twister';
        $minutesMin=8;
        $minutesMax=39;
        $times=7;

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$times,$times);

        $this->assertEquals($times,$runMinutes->count());

    }

    public function testRandomMultipleMinuteRangeBoundary()
    {
        $nowMock=Carbon::createFromDate(2011,6,15)->setTime(23,0);
        $rngEngineSlug='mersenne-twister';
        $minutesMin=7;
        $minutesMax=32;
        $timesMin=10;
        $timesMax=15;

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$timesMin,$timesMax);

        $runMinutesOutOfBoundary=$runMinutes->filter(fn(int $minute)=>$minute>$minutesMax&&$minute<$minutesMin);
        $this->assertEquals(0,$runMinutesOutOfBoundary->count());

    }


    /*
    public function testRandomMultipleMinutesUseCaseFromRedditN1Variant2()
    {
        //https://www.reddit.com/r/laravel/comments/18v714l/comment/ktkyc72/?utm_source=share&utm_medium=web2x&context=3
        //Possible variant #2, since use case is a bit vague:
        //"Do you think I can simply set up a command that runs on weekdays (Monday till Friday) between 8:00 and 18:00 about 4 to 5 times randomly/"humanly" per hour?"
        // Run 4-5 times per hour, only on weekdays (constant, every day), between 08:00 and 18:00 (constant, every hour). Minutes of each hour are random
        $nowMock=Carbon::createFromDate(2024,04,15)->setTime(13,0);
        $rngEngineSlug='mersenne-twister';
        $minutesMin=5;
        $minutesMax=45;
        $timesMin=10;
        $timesMax=15;
        $runMinutes=collect();

        $periodBegin=$nowMock->clone();
        $periodEnd=$nowMock->clone()->next('Friday');

        $period=CarbonPeriod::create($periodBegin, $periodEnd);
        foreach ($period as $date){
            $date->startOfDay()
        }
        for($i=0;$i<=59;$i++){
            $schedule = new Schedule();
            $command=$schedule->command('test')->weekdays()->between('08:00','18:00');
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($nowMock),
                new RNGFactory($rngEngineSlug)
            );

            Carbon::setTestNow($nowMock); //Mock carbon now for Laravel event
            $schedule=$chaoticSchedule->randomMultipleMinutesSchedule($command,$minutesMin,$minutesMax,$timesMin,$timesMax);

            if($schedule->isDue(app())){
                $runMinutes->push($nowMock->minute);
            }
            $nowMock->addminute();
            Carbon::setTestNow();
        }
        return $runMinutes->unique();
        $runMinutesOutOfBoundary=$runMinutes->filter(fn(int $minute)=>$minute>$minutesMax&&$minute<$minutesMin);
        $this->assertEquals(0,$runMinutesOutOfBoundary->count());
    }
    public function testRandomMultipleMinuteX()
    {
        $nowMock=Carbon::createFromDate(2013,11,12)->setTime(18,0);
        $rngEngineSlug='mersenne-twister';
        $minutesMin=5;
        $minutesMax=45;
        $timesMin=10;
        $timesMax=15;

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$timesMin,$timesMax);

        dd($runMinutes);
        $runMinutesOutOfBoundary=$runMinutes->filter(fn(int $minute)=>$minute>$minutesMax&&$minute<$minutesMin);
        $this->assertEquals(0,$runMinutesOutOfBoundary->count());

    }
    */

}