<?php

namespace Tests\Unit;

use App\Weather;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\TestCase;

class WeatherTest extends TestCase
{

    // Permet de clear le Cache entre chaque test pour ne pas fausser les résultats entre les tests
    protected function tearDown(): void
    {
        parent::tearDown();
        Cache::clearResolvedInstances();
    }

    /**
     * On teste s'il fait beau
     * @return void
     */
    public function test_example(): void
    {
        // Approche 2
        $mock = \Mockery::mock(Repository::class);
        $mock->shouldReceive('get')->with('weather')->once()->andReturn(null);
        $weather = new Weather($mock);
        // Approche 1
//        Cache::shouldReceive('get')->with('weather')->once()->andReturn(null);
//        $weather = new Weather();
        $this->assertTrue($weather->isSunnyTomorrow());
    }

    /**
     * On teste s'il ne fait pas beau
     * @return void
     */
    public function test_example_false(): void
    {
        // Approche 2
        $mock = \Mockery::mock(Repository::class);
        $mock->shouldReceive('get')->with('weather')->once()->andReturn(false);
        $weather = new Weather($mock);
        // Approche 1
//        Cache::shouldReceive('get')->with('weather')->once()->andReturn(false);
//        $weather = new Weather();
        $this->assertFalse($weather->isSunnyTomorrow());
    }
}
