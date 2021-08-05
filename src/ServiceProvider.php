<?php

namespace Sidekicker\FlagrFeatureLaravel;

use Flagr\Client\Api\ConstraintApi;
use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\Api\FlagApi;
use Flagr\Client\Api\TagApi;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/features.php' => config_path('features.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateFlagCommand::class
            ]);
        }
    }


    public function register(): void
    {
        $this->registerClasses();
    }

    protected function createGuzzleClient(): Client
    {
        return new Client([
            'base_uri' => config('features.flagr_url'),
            'connect_timeout' => config('features.connect_timeout'),
            'timeout' => config('features.timeout'),
        ]);
    }

    protected function registerClasses(): void
    {
        $this->app->bind(Feature::class, function () {
            return new Feature(
                new EvaluationApi(
                    client: $this->createGuzzleClient()
                )
            );
        });

        $this->app->alias(Feature::class, 'feature');

        $this->app->bind(ConstraintApi::class, function () {
            return new ConstraintApi(
                client: $this->createGuzzleClient()
            );
        });

        $this->app->bind(FlagApi::class, function () {
            return new FlagApi(
                client: $this->createGuzzleClient()
            );
        });

        $this->app->bind(TagApi::class, function () {
            return new TagApi(
                client: $this->createGuzzleClient()
            );
        });
    }
}
