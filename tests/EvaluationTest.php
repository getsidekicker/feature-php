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

    public function testMatch(): void
    {
        $flag = $this->createFlag();
        $feature = app(Feature::class);

        $this->assertTrue($feature->match(
            $flag->getKey()
        ));
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

    private function createFlag(): Flag
    {
        $flagName = uniqid('flag');
        /* @var $flagApi FlagApi */
        $booleanFlag = app(BooleanFlag::class);
        $flag = $booleanFlag->createBooleanFlag($flagName, $flagName);

        $flagApi = app(FlagApi::class);
        $setFlagBody = new SetFlagEnabledRequest();
        $setFlagBody->setEnabled(true);
        $flagApi->setFlagEnabled($setFlagBody, $flag->getId());

        //It takes a while for the flag to be created
        sleep(3);

        return $flag;
    }
}
