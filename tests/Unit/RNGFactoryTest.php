<?php

namespace Skywarth\ChaoticSchedule\Tests\Unit;

use Carbon\Carbon;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;
use UnexpectedValueException;

class RNGFactoryTest extends TestCase
{

    public function test_provides_valid_adapter()
    {
        $factory=new RNGFactory('mersenne-twister');
        $adapter=$factory->getRngEngine();
        $this->assertInstanceOf(RandomNumberGeneratorAdapter::class,$adapter);

    }

    public function test_provides_different_adapter_per_slug()
    {
        $factory1=new RNGFactory('mersenne-twister');
        $adapter1=$factory1->getRngEngine();
        $factory2=new RNGFactory('seed-spring');
        $adapter2=$factory2->getRngEngine();
        $this->assertNotSame($adapter1->getSlug(),$adapter2->getSlug());
        $this->assertNotSame(get_class($adapter1),get_class($adapter2));

    }

    public function test_throws_exception_on_invalid_adapter_slug()
    {
        $factory=new RNGFactory('this_adapter_doesnt_exist');
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Please provide a valid RNG Adapter slug. Example: "mersenne-twister". Check the documentation for details.');
        $adapter=$factory->getRngEngine();

    }

}