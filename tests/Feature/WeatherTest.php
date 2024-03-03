<?php

namespace Tests\Feature;

use App\Weather;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    public function test_with_good_weather(): void
    {
        $this->partialMock(Weather::class, function (MockInterface $mock) {
            $mock->shouldReceive('isSunnyTomorrow')->once()->andReturn(true);
        });
        $response = $this->get('/api/weather');
        $response->assertOk();
        $response->assertJsonPath('weather', 'sunny');
    }

    public function test_with_bad_weather(): void
    {
        $this->partialMock(Weather::class, function (MockInterface $mock) {
            $mock->shouldReceive('isSunnyTomorrow')->once()->andReturn(false);
        });
        $response = $this->get('/api/weather');
        $response->assertOk();
        $response->assertJsonPath('weather', 'sunny');
    }
}
