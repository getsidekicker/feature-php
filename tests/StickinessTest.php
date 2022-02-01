<?php

namespace Sidekicker\FlagrFeature\Tests;

use Sidekicker\FlagrFeature\Feature;

// Test will confirm that entityIds are respected when evaluating
class StickinessTest extends TestCase
{
    use CreateFlagTrait;

    public function testWithId(): void
    {
        $flag = $this->createFlag(50);
        /* @var $feature Feature */
        $feature = app(Feature::class);
        $id = uniqid('id');

        $matches = collect(range(1, 10))
            ->map(fn () => $feature->setId($id)->match(
                $flag['flag']->getKey()
            ))
            ->unique();

        // Expect consistent results across all evaluations
        $this->assertCount(1, $matches);
    }

    public function testWithRandomId(): void
    {
        $flag = $this->createFlag(50);
        /* @var $feature Feature */
        $feature = app(Feature::class);

        $matches = collect(range(1, 10))
            ->map(fn () => $feature->setId(uniqid('id'))->match(
                $flag['flag']->getKey()
            ))
            ->unique();

        // Expect match & non match across the set of 10
        $this->assertCount(2, $matches);
    }
}
