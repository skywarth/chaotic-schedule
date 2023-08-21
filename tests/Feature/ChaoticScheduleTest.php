<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class ChaoticScheduleTest extends TestCase
{
    protected static ChaoticSchedule $chaoticSchedule;
    public static function getChaoticSchedule(){
        if(empty(self::$chaoticSchedule)){
            self::$chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService(),
                new RNGFactory('seed-spring')
            );
        }
        return self::$chaoticSchedule;
    }

    protected const DefaultRNGEngineSlug='mersenne-twister';


    protected function generateRandomTimeConsecutiveDays(int $daysCount,string $rngEngineSlug=self::DefaultRNGEngineSlug):Collection{
        $date=Carbon::now()->startOfDay();
        $schedule = new Schedule();
        $schedule=$schedule->command('foo')->daily();
        $schedules=collect();
        for($i=0;$i<$daysCount;$i++){
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($date),
                new RNGFactory($rngEngineSlug)
            );
            //$designatedRuns->push($nextRun->format('H:i'));
            $schedules->push(clone $chaoticSchedule->randomTimeSchedule($schedule,'06:18','19:42'));
            $date->addDay();
        }
        return $schedules;
    }

    public function test_random_time_incorrect_parameter_format_exception_1()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(InvalidDateFormatException::class);
        $this->getChaoticSchedule()->randomTimeSchedule($schedule,'1000','12:00');

    }

    /*public function test_random_time_incorrect_parameter_format_2()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(InvalidDateFormatException::class);
        $this->getChaoticSchedule()->randomTimeSchedule($schedule,'10:00','55:70');

    }*/

    public function test_random_time_incorrect_parameter_order_exception()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->getChaoticSchedule()->randomTimeSchedule($schedule,'18:00','09:00');
    }

    public function test_random_minute_incorrect_parameter_format_exception()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(\OutOfRangeException::class);
        $this->getChaoticSchedule()->randomMinuteSchedule($schedule,-55,44);
    }
    public function test_random_minute_incorrect_parameter_order_exception()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->getChaoticSchedule()->randomMinuteSchedule($schedule,44,13);
    }

    public function test_random_time_same_command_no_identifier_consistency()
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

    public function test_random_time_same_command_custom_identifier_difference()
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

    public function test_random_time_different_command_no_identifier_difference()
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

    public function test_random_time_different_command_same_custom_identifier_consistency()
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

   /* public function test_random_time_consistency_throughout_the_day()
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
    }*/



    protected function calcChiSquared(Collection $observed, Collection $expected): float {
        $sum = 0;

        foreach ($observed as $index => $value) {
            $sum += pow($value - $expected[$index], 2) / $expected[$index];
        }

        return $sum;
    }
    public function test_random_time_distribution_homogeneity_chi_squared()
    {

        $schedules=$this->generateRandomTimeConsecutiveDays(100);
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

        $chiSquareCriticalValue = 1499; // This value should be checked
        $this->assertLessThan($chiSquareCriticalValue,$chiSquareStat);
    }




}