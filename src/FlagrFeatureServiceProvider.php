<?php

namespace Sidekicker\FlagrFeature;

use Flagr\Client\Api\ConstraintApi;
use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\Api\FlagApi;
use Flagr\Client\Api\TagApi;
use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Http\Request;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FlagrFeatureServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('flagr-feature-laravel')
            ->hasCommand(CreateBooleanFlagCommand::class)
            ->hasConfigFile(['flagr-feature']);
    }

    public function packageRegistered(): void
    {
        $this->registerClasses();
    }

    /**
     * @return array<mixed>
     */
    public function requestContext(): array
    {
        return [
            'env' => $this->app->environment(),
            'user' => $this->app->get(Factory::class)->user()?->toArray() ?? [],
            'host' => $this->app->get(Request::class)->getHost() ?: parse_url(env('APP_URL'), PHP_URL_HOST)
        ];
    }

    public function createGuzzleClient(): Client
    {
        return new Client([
            'base_uri' => config('flagr-feature.flagr_url'),
            'connect_timeout' => config('flagr-feature.connect_timeout'),
            'timeout' => config('flagr-feature.timeout'),
        ]);
    }

    protected function registerClasses(): void
    {
        $this->app->bind(Feature::class, function () {
            $feature = new Feature(
                new EvaluationApi(
                    client: $this->createGuzzleClient()
                )
            );
            $feature->setGlobalContext($this->requestContext());

            return $feature;
        });

        $this->app->alias(Feature::class, 'feature');

        $this->app->bind(ConstraintApi::class, function () {
            return new ConstraintApi(
                client: $this->createGuzzleClient()
            );
        });

        $this->app->bind(FlagApi::class, function () {
            return new class(client: $this->createGuzzleClient()) extends FlagApi {
                /**
                 * @return array<mixed>
                 */
                protected function createHttpClientOption(): array
                {
                    $options = parent::createHttpClientOption();
                    if (config('flagr-feature.auth') === 'basic') {
                        $options['auth'] = [
                            config('flagr-feature.basic.username'),
                            config('flagr-feature.basic.password')
                        ];
                    }

                    return $options;
                }
            };
        });

        $this->app->bind(TagApi::class, function () {
            return new class(client: $this->createGuzzleClient()) extends TagApi {
                /**
                 * @return array<mixed>
                 */
                protected function createHttpClientOption(): array
                {
                    $options = parent::createHttpClientOption();
                    if (config('flagr-feature.auth') === 'basic') {
                        $options['auth'] = [
                            config('flagr-feature.basic.username'),
                            config('flagr-feature.basic.password')
                        ];
                    }

                    return $options;
                }
            };
        });
    }
}
