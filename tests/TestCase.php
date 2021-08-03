<?php

namespace Sidekicker\FlagrFeatureLaravel\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Sidekicker\FlagrFeatureLaravel\ServiceProvider;

class TestCase extends TestbenchTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        config()->set('features.flagr_url', env('FEATURE_FLAGR_URL'));
    }
}
