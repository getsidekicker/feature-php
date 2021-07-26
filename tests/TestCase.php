<?php

namespace Sidekicker\Feature\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Sidekicker\Feature\ServiceProvider;

class TestCase extends TestbenchTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        config()->set('features.flagr_url', env('FEATURE_FLAGR_URL'));
    }
}
