<?php

namespace Sidekicker\Feature\Tests;

use Flagr\Client\Api\FlagApi;
use Flagr\Client\Model\CreateFlagRequest;
use Flagr\Client\Model\Flag;
use Flagr\Client\Model\SetFlagEnabledRequest;
use Sidekicker\Feature\CreateFlag;
use Sidekicker\Feature\Feature;

// Test class that will test the CreateFlag class
class EvaluationTest extends TestCase
{

    // Test that the CreateFlag class can be instantiated
    public function testEvaluation()
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

    public function testNoMatchEvaluation()
    {
        $feature = app(Feature::class);
        $evaluated = false;
        $feature->evaluate(
            'no_existent_flag',
            _: function () use (&$evaluated) {
                $evaluated = true;
            }
        );
        $this->assertTrue($evaluated);
    }

    public function testMatch()
    {
        $flag = $this->createFlag();
        $feature = app(Feature::class);

        $this->assertTrue($feature->match(
            $flag->getKey()
        ));
    }

    public function testFunctionsExported()
    {
        $this->assertTrue(function_exists('feature_eval'));
        $this->assertTrue(function_exists('feature_match'));
    }

    private function createFlag(): Flag
    {
        $flagName = uniqid('flag');
        /* @var $flagApi FlagApi */
        $createFlag = app(CreateFlag::class);
        $flag = $createFlag->createFlag($flagName, $flagName);

        $flagApi = app(FlagApi::class);
        $setFlagBody = new SetFlagEnabledRequest();
        $setFlagBody->setEnabled(true);
        $flagApi->setFlagEnabled($setFlagBody, $flag->getId());

        //It takes a while for the flag to be created
        sleep(3);

        return $flag;
    }
}
