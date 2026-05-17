<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule\RandomTimeMacros;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule\AbstractChaoticScheduleTest;

class RandomTimeScheduleTest extends AbstractChaoticScheduleTest
{

    public function testRandomTimeIncorrectParameterFormatException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(InvalidDateFormatException::class);
        $this->makeChaoticSchedule()->randomTimeSchedule($schedule,'1000','12:00');

    }

    public function testRandomTimeClosureParamEvenNumbersOnly()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $runDates=collect();
        for ($i=0;$i<25;$i++){
            $date=$this->makeChaoticSchedule()->randomTimeSchedule($schedule,'09:06','09:44','test'.$i,function (int $motd){
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


    public function testRandomTimeClosureThrowsIncompatibleClosureResponseException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncompatibleClosureResponse::class);
        $this->makeChaoticSchedule()->randomTimeSchedule($schedule,'09:00','15:00','test2',function (int $motd){
            return [];
        });
    }

    public function testRandomTimeIncorrectParameterOrderException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->makeChaoticSchedule()->randomTimeSchedule($schedule,'18:00','09:00');
    }

    public function testRandomTimeSameCommandCustomIdentifierDifference()
    {
        // Pin the basis so this is a deterministic claim, not a 1/240 coin flip:
        // randomTimeSchedule picks one minute in 09:00–13:00 (240 values), so two
        // distinct seeds collide ~0.4% of basis-days. Verified at this date.
        // Use one ChaoticSchedule across both macro calls to mirror the
        // production singleton binding.
        Carbon::setTestNow(Carbon::create(2024, 4, 13, 9, 42, 0));
        $chaoticSchedule = $this->makeChaoticSchedule();

        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->wednesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->wednesdays();
        $comm1NextRun=$chaoticSchedule->randomTimeSchedule($comm1,'09:00','13:00')->nextRunDate();
        $comm2NextRun=$chaoticSchedule->randomTimeSchedule($comm2,'09:00','13:00','testing')->nextRunDate();
        $datesEqual=$comm1NextRun->notEqualTo($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomTimeDifferentCommandNoIdentifierDifference()
    {
        // Pin the basis — same reasoning as testRandomTimeSameCommandCustomIdentifierDifference.
        // One ChaoticSchedule instance mirrors the production singleton.
        Carbon::setTestNow(Carbon::create(2024, 4, 13, 9, 42, 0));
        $chaoticSchedule = $this->makeChaoticSchedule();

        $schedule1 = new Schedule();
        $comm1=$schedule1->command('foo')->wednesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('bar')->wednesdays();
        $comm1NextRun=$chaoticSchedule->randomTimeSchedule($comm1,'09:00','13:00')->nextRunDate();
        $comm2NextRun=$chaoticSchedule->randomTimeSchedule($comm2,'09:00','13:00')->nextRunDate();
        $datesEqual=$comm1NextRun->notEqualTo($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }


    public function testRandomTimeDifferentCommandSameCustomIdentifierConsistency()
    {
        // Pin the basis and reuse one ChaoticSchedule instance — mirrors the
        // production singleton binding (ChaoticScheduleServiceProvider binds
        // ChaoticSchedule as singleton, one per schedule:run process).
        Carbon::setTestNow(Carbon::create(2024, 4, 13, 9, 42, 0));
        $chaoticSchedule = $this->makeChaoticSchedule();

        $schedule1 = new Schedule();
        $comm1=$schedule1->command('foo')->wednesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('bar')->wednesdays();
        $comm1NextRun=$chaoticSchedule->randomTimeSchedule($comm1,'09:00','13:00','testing')->nextRunDate();
        $comm2NextRun=$chaoticSchedule->randomTimeSchedule($comm2,'09:00','13:00','testing')->nextRunDate();
        $datesEqual=$comm1NextRun->eq($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomTimeConsistencyThroughoutTheDay()
    {

        //$chaoticSchedule=$this->makeChaoticSchedule();

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

    public function testRandomTimeSameCommandNoIdentifierConsistency()
    {
        // Pin the basis and reuse one ChaoticSchedule — same reasoning as
        // testRandomTimeDifferentCommandSameCustomIdentifierConsistency.
        Carbon::setTestNow(Carbon::create(2024, 4, 13, 9, 42, 0));
        $chaoticSchedule = $this->makeChaoticSchedule();

        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->wednesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->wednesdays();
        $comm1NextRun=$chaoticSchedule->randomTimeSchedule($comm1,'09:00','13:00')->nextRunDate();
        $comm2NextRun=$chaoticSchedule->randomTimeSchedule($comm2,'09:00','13:00')->nextRunDate();
        $datesEqual=$comm1NextRun->eq($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }
}
