<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;
use Skywarth\ChaoticSchedule\Enums\RandomDateScheduleBasis;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidScheduleBasisProvided;
use Skywarth\ChaoticSchedule\Exceptions\RunTimesExpectationCannotBeMet;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;
use TypeError;

class RandomDateMacrosTest extends AbstractChaoticScheduleTest
{


    protected function randomDateScheduleTestingBoilerplate(Carbon $nowMock, int $periodType, array $daysOfWeek ,$timesMin, $timesMax,string $rngEngineSlug, ?callable $closure=null):Collection{

        //assertion
        $periodBegin=$nowMock->clone()->startof(RandomDateScheduleBasis::getString($periodType));
        $periodEnd=$nowMock->clone()->endOf(RandomDateScheduleBasis::getString($periodType));

        $period=CarbonPeriod::create($periodBegin, $periodEnd);


        $runDates=collect();
        foreach ($period as $index=>$date){
            $schedule = new Schedule();
            $schedule=$schedule->command('test');
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($date),
                new RNGFactory($rngEngineSlug)
            );
            $schedule=$chaoticSchedule->randomDaysSchedule($schedule,$periodType,$daysOfWeek,$timesMin,$timesMax,null,$closure);



            Carbon::setTestNow($date); //Mock carbon now for Laravel event
            if($schedule->isDue(app())){

                $runDates->push($date);

                $this->assertTrue($date->isBetween($periodBegin,$periodEnd),'Generated random run date is not in the range');
                $this->assertContains($date->dayOfWeek,$daysOfWeek);

            }
            Carbon::setTestNow();//resetting the carbon::now to original
        }

        $this->assertGreaterThanOrEqual($timesMin,$runDates->count());
        $this->assertLessThanOrEqual($timesMax,$runDates->count());
        return $runDates;
    }


    public function testWeekBasisAllDOWExactTimes()
    {

        $nowMock=Carbon::createFromDate(2023,6,01);//Thursday
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=4;
        $timesMax=4;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');

    }



    public function testWeekBasisSelectiveDOWExactTimes()
    {
        $nowMock=Carbon::createFromDate(2023,6,01);//Thursday
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=3;
        $timesMax=3;
        $daysOfWeek=[Carbon::MONDAY,Carbon::SATURDAY,Carbon::THURSDAY,Carbon::FRIDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');

    }

    public function testWeekBasisSelectiveDowRandomTimes()
    {
        $nowMock=Carbon::createFromDate(2006,02,13);
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=1;
        $timesMax=4;
        $daysOfWeek=[Carbon::TUESDAY,Carbon::SATURDAY,Carbon::THURSDAY,Carbon::FRIDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');

    }

    public function testWeekBasisAllDowRandomTimes()
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
    public function testMonthBasisAllDowExactTimes()
    {
        $nowMock=Carbon::createFromDate(2018,03,22);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=7;
        $timesMax=7;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');
    }


    public function testMonthBasisSelectiveDowExactTimes()
    {
        $nowMock=Carbon::createFromDate(2018,03,22);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=12;
        $timesMax=12;
        $daysOfWeek=[Carbon::MONDAY,Carbon::WEDNESDAY,Carbon::THURSDAY,Carbon::SUNDAY];//Sunday Lunch...
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }


    public function testMonthBasisAllDowRandomTimes()
    {
        $nowMock=Carbon::createFromDate(2015,06,25);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=10;
        $timesMax=25;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testMonthBasisSelectiveDowRandomTimes()
    {
        $nowMock=Carbon::createFromDate(2022,10,01);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=0;
        $timesMax=9;
        $daysOfWeek=[Carbon::THURSDAY,Carbon::TUESDAY,Carbon::SATURDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testMonthBasisSelectiveDowRandomTimesOnlyOddDays()
    {
        $nowMock=Carbon::createFromDate(2017,04,07);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=0;
        $timesMax=4;
        $daysOfWeek=[Carbon::THURSDAY,Carbon::TUESDAY,Carbon::SATURDAY];
        $closure=function (Collection $dates){
              return $dates->filter(function (Carbon $date){
                  return $date->day%2!==0;//odd numbered days only
              });
        };
        $runDates=$this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring',$closure);
        $nonOddNumberDayRuns=$runDates->filter(function (Carbon $date){
            return $date->day%2===0;
        });
        $this->assertEmpty($nonOddNumberDayRuns);
    }


    /*
     ██    ██ ███████  █████  ██████      ██████   █████  ███████ ██ ███████
      ██  ██  ██      ██   ██ ██   ██     ██   ██ ██   ██ ██      ██ ██
       ████   █████   ███████ ██████      ██████  ███████ ███████ ██ ███████
        ██    ██      ██   ██ ██   ██     ██   ██ ██   ██      ██ ██      ██
        ██    ███████ ██   ██ ██   ██     ██████  ██   ██ ███████ ██ ███████
     */

    public function testYearBasisAllDowExactTimes()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=50;
        $timesMax=50;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');
    }

    public function testYearBasisSelectiveDowExactTimes()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=6;
        $timesMax=6;
        $daysOfWeek=[Carbon::WEDNESDAY,Carbon::THURSDAY,Carbon::SUNDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testYearBasisAllDowRandomTimes()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=7;
        $timesMax=33;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testYearBasisSelectiveDowRandomTimes()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=0;
        $timesMax=22;
        $daysOfWeek=[Carbon::MONDAY];
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');
    }

    public function testYearBasisSelectiveDowExactTimesBufferViaClosure()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=10;
        $timesMax=10;
        $daysOfWeek=[Carbon::SATURDAY,Carbon::SUNDAY];

        $bufferWeeks=4;


        //Some example case of applying buffer via ruling out those that don't fall outside the buffer
        $closure=function (Collection $runDates) use ($bufferWeeks){
            $latestDate=null;
          return $runDates->sortBy(function (Carbon $date){
              return $date->timestamp;
          })->filter(function (Carbon $date) use (&$latestDate,$bufferWeeks){
                 if(empty($latestDate) || $date->diffInWeeks($latestDate)>=$bufferWeeks){
                     $latestDate=$date;
                     return true;
                 }else{
                     return false;
                 }
          });
        };
        $runDates=$this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring',$closure);

        //assertion
        $lastDate=null;
        foreach ($runDates as $runDate){
            if(!empty($lastDate)){
                $diffInWeeks=$runDate->diffInWeeks($lastDate);
                $this->assertTrue($diffInWeeks>=$bufferWeeks);
            }

            $lastDate=$runDate;
        }
    }


    /*
     * -----------------------------------------------------------------------------------------------------------------
     */


    public function testMonthBasisTimesMaxExceedsPossibleRuns()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=0;
        $timesMax=20;//Max possible here is actually `9`
        $daysOfWeek=[Carbon::MONDAY,Carbon::SUNDAY];
        $this->expectException(RunTimesExpectationCannotBeMet::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');
    }

    public function testMonthBasisAllDowTimesMaxExceedsPossibleRuns()
    {
        $nowMock=Carbon::createFromDate(2019,07,02);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=0;
        $timesMax=32;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->expectException(RunTimesExpectationCannotBeMet::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testWeekBasisTimesMaxExceedsPossibleRuns()
    {
        $nowMock=Carbon::createFromDate(2023,10,11);
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=0;
        $timesMax=2;//Because there is only 1 thursday in a week, duh.
        $daysOfWeek=[Carbon::THURSDAY];
        $this->expectException(RunTimesExpectationCannotBeMet::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }


    public function testYearBasisTimesMaxExceedsPossibleRuns()
    {
        $nowMock=Carbon::createFromDate(2023,10,11);
        $periodType=RandomDateScheduleBasis::YEAR;
        $timesMin=0;
        $timesMax=200;
        $daysOfWeek=[Carbon::THURSDAY,Carbon::FRIDAY,Carbon::SUNDAY];
        $this->expectException(RunTimesExpectationCannotBeMet::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister');
    }

    public function testInvalidClosureReturnType()
    {
        $nowMock=Carbon::createFromDate(2020,8,9);
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=0;
        $timesMax=2;
        $daysOfWeek=[Carbon::THURSDAY,Carbon::FRIDAY,Carbon::SUNDAY];
        $closure=function (Collection $dates){
              return false;
        };
        $this->expectException(IncompatibleClosureResponse::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'mersenne-twister',$closure);
    }

    public function testInvalidTimesValues()
    {
        $nowMock=Carbon::createFromDate(2020,8,9);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=-5;
        $timesMax=10;
        $daysOfWeek=[];
        $this->expectException(LogicException::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testInvalidTimesRange()
    {
        $nowMock=Carbon::createFromDate(2020,8,9);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=10;
        $timesMax=5;
        $daysOfWeek=[];
        $this->expectException(IncorrectRangeException::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testInvalidDowParameterType()
    {
        $nowMock=Carbon::createFromDate(2020,8,9);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=0;
        $timesMax=3;
        $daysOfWeek=Carbon::SATURDAY;
        $this->expectException(TypeError::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testInvalidDowValues()
    {
        $nowMock=Carbon::createFromDate(2020,8,9);
        $periodType=RandomDateScheduleBasis::MONTH;
        $timesMin=0;
        $timesMax=10;
        $daysOfWeek=[Carbon::TUESDAY,'sunday','monday'];
        $this->expectException(InvalidArgumentException::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testInvalidPeriodTypeParameterType()
    {
        $nowMock=Carbon::createFromDate(2020,8,9);
        $periodType='weekly';
        $timesMin=4;
        $timesMax=4;
        $daysOfWeek=[];
        $this->expectException(TypeError::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

    public function testInvalidPeriodTypeValue()
    {
        $nowMock=Carbon::createFromDate(2020,8,9);
        $periodType=50;
        $timesMin=4;
        $timesMax=4;
        $daysOfWeek=ChaoticSchedule::ALL_DOW;
        $this->expectException(InvalidScheduleBasisProvided::class);
        $this->randomDateScheduleTestingBoilerplate($nowMock,$periodType,$daysOfWeek,$timesMin,$timesMax,'seed-spring');
    }

}