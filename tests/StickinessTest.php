<?php

namespace Sidekicker\FlagrFeature\Tests;

use Flagr\Client\Model\Flag;
use Sidekicker\FlagrFeature\Feature;

// Test will confirm that entityIds are respected when evaluating
class StickinessTest extends TestCase
{
    use CreateFlagTrait;

    public function testWithId(): void
    {
        /** @var Flag $flag */
        $flag = $this->createFlag(rollout: 50)['flag'];
        /** @var string $key */
        $key = $flag->getKey();

        /** @var Feature $feature */
        $feature = app(Feature::class);
        $id = uniqid('id');

        $matches = collect()
            ->range(1, 10)
            ->map(fn () => $feature->setId("{$id}")->match($key))
            ->unique();

        // Expect consistent results across all evaluations
        $this->assertCount(1, $matches);
    }

    public function testWithRandomId(): void
    {
        /** @var Flag $flag */
        $flag = $this->createFlag(rollout: 50)['flag'];
        /** @var string $key */
        $key = $flag->getKey();

        /** @var Feature $feature */
        $feature = app(Feature::class);

        $matches = collect()
            ->range(1, 10)
            ->map(fn () => $feature->setId(uniqid('id'))->match($key))
            ->unique();

        // Expect match and non match across the set of 10
        $this->assertCount(2, $matches);
    }
}
