<?php

namespace Sidekicker\FlagrFeature\Tests;

use Sidekicker\FlagrFeature\Feature;

// Test class that will test the CreateFlag class
class EvaluationTest extends TestCase
{
    use CreateFlagTrait;

    // Test that the CreateFlag class can be instantiated
    public function testEvaluation(): void
    {
        $flag = $this->createFlag()['flag'];
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
            'non_existent_flag',
            otherwise: function () use (&$evaluated) {
                $evaluated = true;
            }
        );
        $this->assertTrue($evaluated);
    }

    public function testMatchVariant(): void
    {
        $flag = $this->createFlag()['flag'];
        $flag2 = $this->createFlag()['flag'];
        $feature = app(Feature::class);

        $this->assertFalse($feature->match(
            flag: $flag->getKey(),
            matchVariant: 'random'
        ));

        $this->assertTrue($feature->match(
            flag: $flag->getKey(),
            matchVariant: 'on'
        ));

        $this->assertTrue($feature->match(
            flag: $flag2->getKey(),
            matchVariant: 'on'
        ));
    }

    public function testMatchAttachment(): void
    {
        $flag = $this->createFlag()['flag'];
        $feature = app(Feature::class);
        $matchAttachment = ['something'];

        $this->assertTrue($feature->match(
            flag: $flag->getKey(),
            matchAttachment: $matchAttachment
        ));
        $this->assertEmpty($matchAttachment);
    }

    public function testTags(): void
    {
        $flag = $this->createFlag();
        config()->set('flagr-feature.tags', [$flag['tag']]);
        $feature = app(Feature::class);

        $this->assertTrue($feature->match(
            flag: $flag['flag']->getKey()
        ));

        config()->set('flagr-feature.tags', []);
    }

    public function testNonPresentTags(): void
    {
        $flag = $this->createFlag()['flag'];

        config()->set('flagr-feature.tags', ['non-matching-tag']);
        $feature = app(Feature::class);

        $this->assertFalse($feature->match(
            flag: $flag->getKey()
        ));

        config()->set('flagr-feature.tags', []);
    }

    public function testAnyTagOperator(): void
    {
        $flag = $this->createFlag();

        config()->set('flagr-feature.tag_operator', 'ANY');
        config()->set('flagr-feature.tags', [$flag['tag'], 'non-matching-tag']);
        $feature = app(Feature::class);

        $this->assertTrue($feature->match(
            flag: $flag['flag']->getKey()
        ));

        config()->set('flagr-feature.tags', []);
    }

    public function testAndTagOperator(): void
    {
        $flag = $this->createFlag();

        config()->set('flagr-feature.tag_operator', 'ALL');
        config()->set('flagr-feature.tags', [$flag['tag'], 'non-matching-tag']);
        $feature = app(Feature::class);

        $this->assertFalse($feature->match(
            flag: $flag['flag']->getKey()
        ));
    }
}
