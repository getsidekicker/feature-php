<?php

namespace Sidekicker\FlagrFeature\Tests;

use Flagr\Client\Api\FlagApi;
use Flagr\Client\Model\Flag;
use Flagr\Client\Model\SetFlagEnabledRequest;
use Sidekicker\FlagrFeature\BooleanFlag;
use Sidekicker\FlagrFeature\Feature;

// Test class that will test the CreateFlag class
class EvaluationTest extends TestCase
{
    // Test that the CreateFlag class can be instantiated
    public function testEvaluation(): void
    {
        $flag = $this->createFlag();
        $feature = app(Feature::class);
        $evaluated = false;
        $feature->evaluate(
            $flag->getKey(),
            on: function () use (&$evaluated) {
                $evaluated = true;
            }
        );
        $this->assertTrue($evaluated);
    }

    public function testNoMatchEvaluation(): void
    {
        $feature = app(Feature::class);
        $evaluated = false;
        $feature->evaluate(
            'no_existent_flag',
            otherwise: function () use (&$evaluated) {
                $evaluated = true;
            }
        );
        $this->assertTrue($evaluated);
    }

    public function testMatchVariant(): void
    {
        $flag = $this->createFlag();
        $feature = app(Feature::class);

        $this->assertFalse($feature->match(
            flag: $flag->getKey(),
            matchVariant: 'random'
        ));

        $this->assertTrue($feature->match(
            flag: $flag->getKey(),
            matchVariant: 'on'
        ));
    }

    public function testMatchAttachment(): void
    {
        $flag = $this->createFlag();
        $feature = app(Feature::class);
        $matchAttachment = ['something'];

        $this->assertTrue($feature->match(
            flag: $flag->getKey(),
            matchAttachment: $matchAttachment
        ));
        $this->assertNull($matchAttachment);
    }

    public function testTags(): void
    {
        $flag = $this->createFlag();
        config()->set('flagr-feature.tags', ['tag']);
        $feature = app(Feature::class);

        $this->assertTrue($feature->match(
            flag: $flag->getKey()
        ));

        config()->set('flagr-feature.tags', ['non-matching-tag']);
        $feature = app(Feature::class);
        $this->assertFalse($feature->match(
            flag: $flag->getKey()
        ));

        config()->set('flagr-feature.tags', []);
    }

    private function createFlag(): Flag
    {
        $flagName = uniqid('flag');
        /* @var $flagApi FlagApi */
        $booleanFlag = app(BooleanFlag::class);
        $flag = $booleanFlag->createBooleanFlag($flagName, $flagName, ['tag']);

        $flagApi = app(FlagApi::class);
        $setFlagBody = new SetFlagEnabledRequest();
        $setFlagBody->setEnabled(true);
        $flagApi->setFlagEnabled($flag->getId(), $setFlagBody);

        //It takes a while for the flag to be created
        sleep(3);

        return $flag;
    }
}
