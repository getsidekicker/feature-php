<?php

namespace Sidekicker\FlagrFeature\Tests;

use Sidekicker\FlagrFeature\FlagrFeatureServiceProvider;

// Test class that will test the CreateFlag class
class ServiceProviderTest extends TestCase
{
    public function testRequestContext(): void
    {
        $serviceProvider = app(FlagrFeatureServiceProvider::class);
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
    }

    public function testCreateGuzzleClient(): void
    {
        $serviceProvider = app(FlagrFeatureServiceProvider::class);
        $client = $serviceProvider->createGuzzleClient();
        $this->assertInstanceOf(\GuzzleHttp\Client::class, $client);
        $this->assertEquals('http://flagr.local', $client->getConfig()['base_uri']);
        $this->assertEquals('timeout', $client->getConfig()['timeout']);
        $this->assertEquals('connect_timeout', $client->getConfig()['connect_timeout']);
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
