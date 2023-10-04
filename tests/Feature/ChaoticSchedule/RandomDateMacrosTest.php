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
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class RandomDateMacrosTest extends AbstractChaoticScheduleTest
{


    protected function randomDateScheduleTestingBoilerplate(Carbon $nowMock, int $periodType, array $daysOfWeek ,$timesMin, $timesMax,string $rngEngineSlug,bool $consistencyTest=false){

        //assertion
        $periodBegin=$nowMock->clone()->startof(RandomDateScheduleBasis::getString($periodType));
        $periodEnd=$nowMock->clone()->endOf(RandomDateScheduleBasis::getString($periodType));

        $period=CarbonPeriod::create($periodBegin, $periodEnd);


        $runsCounter=0;
        foreach ($period as $index=>$date){
            $schedule = new Schedule();
            $schedule=$schedule->command('test');
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($date),
                new RNGFactory($rngEngineSlug)
            );
            $schedule=$chaoticSchedule->randomDaysSchedule($schedule,$periodType,$daysOfWeek,$timesMin,$timesMax);



            Carbon::setTestNow($date); //Mock carbon now for Laravel event
            if($schedule->isDue(app())){

                $runsCounter++;

                $this->assertTrue($date->isBetween($periodBegin,$periodEnd),'Generated random run date is not in the range');
                $this->assertContains($date->dayOfWeek,$daysOfWeek);

            }
            Carbon::setTestNow();//resetting the carbon::now to original
        }

        $this->assertGreaterThanOrEqual($timesMin,$runsCounter);
        $this->assertLessThanOrEqual($timesMax,$runsCounter);
    }


    public function test_week_basis_all_DOW_exact_times()
    {

        $nowMock=Carbon::createFromDate(2023,6,01);//Thursday
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=4;
        $timesMax=4;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');

    }



    public function test_week_basis_selective_DOW_exact_times()
    {
        $nowMock=Carbon::createFromDate(2023,6,01);//Thursday
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=3;
        $timesMax=3;
        $daysOfWeek=[Carbon::MONDAY,Carbon::SATURDAY,Carbon::THURSDAY,Carbon::FRIDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');

    }

    public function test_week_basis_selective_DOW_random_times()
    {
        $nowMock=Carbon::createFromDate(2006,02,13);
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=1;
        $timesMax=4;
        $daysOfWeek=[Carbon::TUESDAY,Carbon::SATURDAY,Carbon::THURSDAY,Carbon::FRIDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');

    }

    public function test_week_basis_all_DOW_random_times()
    {
        $nowMock=Carbon::createFromDate(2011,12,31);
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=2;
        $timesMax=5;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');

    }



    /*
        ███    ███  ██████  ███    ██ ████████ ██   ██             ██████   █████  ███████ ██ ███████
        ████  ████ ██    ██ ████   ██    ██    ██   ██             ██   ██ ██   ██ ██      ██ ██
        ██ ████ ██ ██    ██ ██ ██  ██    ██    ███████             ██████  ███████ ███████ ██ ███████
        ██  ██  ██ ██    ██ ██  ██ ██    ██    ██   ██             ██   ██ ██   ██      ██ ██      ██
        ██      ██  ██████  ██   ████    ██    ██   ██             ██████  ██   ██ ███████ ██ ███████
     */
    public function test_month_basis_all_DOW_exact_times()
    {
        $nowMock=Carbon::createFromDate(2018,03,22);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=7;
        $timesMax=7;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');
    }


    public function test_month_basis_selective_DOW_exact_times()
    {
        $nowMock=Carbon::createFromDate(2018,03,22);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=12;
        $timesMax=12;
        $daysOfWeek=[Carbon::MONDAY,Carbon::WEDNESDAY,Carbon::THURSDAY,Carbon::SUNDAY];//Sunday Lunch...
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }


    public function test_month_basis_all_DOW_random_times()
    {
        $nowMock=Carbon::createFromDate(2015,06,25);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=10;
        $timesMax=25;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function test_month_basis_selective_DOW_random_times()
    {
        $nowMock=Carbon::createFromDate(2022,10,01);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=0;
        $timesMax=9;
        $daysOfWeek=[Carbon::THURSDAY,Carbon::TUESDAY,Carbon::SATURDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }


    /*
     ██    ██ ███████  █████  ██████      ██████   █████  ███████ ██ ███████
      ██  ██  ██      ██   ██ ██   ██     ██   ██ ██   ██ ██      ██ ██
       ████   █████   ███████ ██████      ██████  ███████ ███████ ██ ███████
        ██    ██      ██   ██ ██   ██     ██   ██ ██   ██      ██ ██      ██
        ██    ███████ ██   ██ ██   ██     ██████  ██   ██ ███████ ██ ███████
     */

    public function test_year_basis_all_DOW_exact_times()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=50;
        $timesMax=50;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');
    }

    public function test_year_basis_selective_DOW_exact_times()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=6;
        $timesMax=6;
        $daysOfWeek=[Carbon::WEDNESDAY,Carbon::THURSDAY,Carbon::SUNDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function test_year_basis_all_DOW_random_times()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=7;
        $timesMax=33;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function test_year_basis_selective_DOW_random_times()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=0;
        $timesMax=22;
        $daysOfWeek=[Carbon::MONDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');
    }

}