<?php

namespace Sidekicker\FlagrFeature\Tests;

use Illuminate\Http\Request;
use Sidekicker\FlagrFeature\Feature;
use Sidekicker\FlagrFeature\FlagrFeatureServiceProvider;

// Test class that will test the CreateFlag class
class ServiceProviderTest extends TestCase
{
    public function testRequestContext(): void
    {
        $serviceProvider = new FlagrFeatureServiceProvider($this->app);
        $this->assertEquals(
            [
                'env' => 'testing',
                'user' => [
                    'id' => 1,
                    'username' => 'user'
                ],
                'url' => 'flagr.local'
            ],
            $serviceProvider->requestContext()
        );


        $this->app->instance('request', Request::capture());
        $this->assertEquals(
            'flagr.request.local',
            $serviceProvider->requestContext()['url']
        );
    }

    public function testFunctionsExported(): void
    {
        $this->assertTrue(function_exists('feature_eval'));
        $this->assertTrue(function_exists('feature_match'));
    }

    public function testAlias(): void
    {
        $this->assertInstanceOf(Feature::class, app('feature'));
    }
}
