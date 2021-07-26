<?php

namespace Sidekicker\Feature;

use Flagr\Client\Api\ConstraintApi;
use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\Api\FlagApi;
use Flagr\Client\Api\TagApi;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Sidekicker\Feature\Feature;

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
        $client = fn () => new Client([
            'base_uri' => config('features.flagr_url')
        ]);

        $this->app->bind(Feature::class, function () use ($client) {
            return new Feature(
                new EvaluationApi(
                    client: $client()
                )
            );
        });

        $this->app->bind(ConstraintApi::class, function () use ($client) {
            return new ConstraintApi(
                client: $client()
            );
        });

        $this->app->bind(FlagApi::class, function () use ($client) {
            return new FlagApi(
                client: $client()
            );
        });

        $this->app->bind(TagApi::class, function () use ($client) {
            return new TagApi(
                client: $client()
            );
        });
    }
}
