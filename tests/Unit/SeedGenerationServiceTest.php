<?php

namespace Skywarth\ChaoticSchedule\Tests\Unit;

use Carbon\Carbon;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class SeedGenerationServiceTest extends TestCase
{

    private SeedGenerationService $service;
    protected function getServiceInstance():SeedGenerationService{
        if(empty($this->service)){
            $this->service=new SeedGenerationService();
        }
        return $this->service;
    }

    public function testSeedForDaySameIdentifier()
    {
        $identifier='my-unique-identifier';

        $this->assertSame($this->getServiceInstance()->seedForDay($identifier),$this->getServiceInstance()->seedForDay($identifier));
    }

    public function testSeedForDayDifferentIdentifier()
    {
        $this->assertNotSame(
            $this->getServiceInstance()->seedForDay('dog'),
            $this->getServiceInstance()->seedForDay('cat')
        );
    }

    public function testSeedForDayDiffersPerDate()
    {
        $seeds=collect();
        $id='test';
        $this->getServiceInstance()->setBasisDate(Carbon::now()->addDay());
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        $this->getServiceInstance()->setBasisDate(Carbon::now()->addDays(3));
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        $this->getServiceInstance()->setBasisDate(Carbon::now()->addWeeks(2));
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        $this->getServiceInstance()->setBasisDate(Carbon::now()->addMonth());
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        $this->getServiceInstance()->setBasisDate(Carbon::now()->addYear());
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        //assert that all of them are different and unique
        $this->assertSame($seeds->toArray(),$seeds->unique()->toArray());
    }

    public function testSeedForDayFormat(){

        for($i=0;$i<30;$i++){
            $seed=$this->getServiceInstance()->seedForDay($i);
            $this->assertEquals(SeedGenerationService::SEED_LENGTH,strlen($seed));
        }

    }



}