<?php

namespace Sidekicker\FlagrFeature\Tests;

use Illuminate\Http\Request;
use ReflectionFunction;
use ReflectionMethod;
use Sidekicker\FlagrFeature\Feature;
use Sidekicker\FlagrFeature\FlagrFeatureServiceProvider;

/**
 * @property-read \Illuminate\Foundation\Application $app
 */
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
                'host' => 'flagr.local'
            ],
            $serviceProvider->requestContext()
        );


        $this->app->instance('request', Request::capture());
        $this->assertEquals(
            'flagr.request.local',
            $serviceProvider->requestContext()['host']
        );
    }

    public function testFunctionsExported(): void
    {
        $this->assertTrue(function_exists('feature_eval'));
        $this->assertTrue(function_exists('feature_match'));
        $this->assertTrue(function_exists('feature_add_context'));
    }

    public function testEvalSignature(): void
    {
        $evalFunction = new ReflectionFunction('feature_eval');
        $evalMethod = new ReflectionMethod(Feature::class, 'evaluate');
        $this->compareParameters($evalMethod->getParameters(), $evalFunction->getParameters());
    }

    public function testMatchSignature(): void
    {
        $evalFunction = new ReflectionFunction('feature_match');
        $evalMethod = new ReflectionMethod(Feature::class, 'match');
        $this->compareParameters($evalMethod->getParameters(), $evalFunction->getParameters());
    }

    public function testAddContextSignature(): void
    {
        $evalFunction = new ReflectionFunction('feature_add_context');
        $evalMethod = new ReflectionMethod(Feature::class, 'addContext');
        $this->compareParameters($evalMethod->getParameters(), $evalFunction->getParameters());
    }

    public function testAlias(): void
    {
        $this->assertInstanceOf(Feature::class, app('feature'));
    }

    public function testSingleton(): void
    {
        $this->assertEquals(app(Feature::class), app('feature'));
    }

    /**
     * @param \ReflectionParameter[] $a
     * @param \ReflectionParameter[] $b
     * @return void
     */
    private function compareParameters(array $a, array $b): void
    {
        $this->assertTrue(count($a) === count($b), 'Parameters count does not match');
        foreach ($a as $index => $parameter) {
            $this->assertEquals($parameter->getName(), $b[$index]->getName(), 'Parameter names do not match');
            $this->assertEquals($parameter->getType(), $b[$index]->getType(), 'Parameter types do not match');
            $this->assertEquals($parameter->isOptional(), $b[$index]->isOptional(), 'Parameter optional flags do not match');
            $this->assertEquals($parameter->getType(), $b[$index]->getType(), 'Parameter types do not match');
            $this->assertEquals($parameter->isPassedByReference(), $b[$index]->isPassedByReference(), 'Parameter reference flags do not match');
            $this->assertEquals($parameter->isDefaultValueAvailable(), $b[$index]->isDefaultValueAvailable(), 'Parameter default value flags do not match');
            $this->assertEquals($parameter->isVariadic(), $b[$index]->isVariadic(), 'Parameter variadic flags do not match');
        }
    }
}
