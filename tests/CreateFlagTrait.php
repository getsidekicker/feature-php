<?php

namespace Sidekicker\FlagrFeature\Tests;

use Flagr\Client\Api\FlagApi;
use Flagr\Client\Model\Flag;
use Flagr\Client\Model\SetFlagEnabledRequest;
use Sidekicker\FlagrFeature\BooleanFlag;

trait CreateFlagTrait
{
    /**
     * @return array{flag: Flag, tag: string}
     */
    private function createFlag(int $rollout = 100): array
    {
        $flagName = uniqid('flag');
        $tag = uniqid('tag');
        /* @var $flagApi FlagApi */
        $booleanFlag = app(BooleanFlag::class);
        $flag = $booleanFlag->createBooleanFlag($flagName, $flagName, [$tag], $rollout);

        $flagApi = app(FlagApi::class);
        $setFlagBody = new SetFlagEnabledRequest();
        $setFlagBody->setEnabled(true);
        $flagApi->setFlagEnabled($flag->getId(), $setFlagBody);

        //It takes a while for the flag to be created
        sleep(3);

        return ['flag' => $flag, 'tag' => $tag];
    }
}
