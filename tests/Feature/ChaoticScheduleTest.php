<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
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


}