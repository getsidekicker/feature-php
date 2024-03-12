<?php

namespace Sidekicker\FlagrFeature\Tests;

use Sidekicker\FlagrFeature\BooleanFlag;
use Sidekicker\FlagrFeature\Feature;

class VariantTest extends TestCase
{
    use CreateFlagTrait;

    public function testFindVariants(): void
    {
        $attachment = [
            "1-3" => 15,
            "4-10" => 18,
            "11-20" => 22,
            "21+" => 28,
            "lastMinuteJobMinimum" => 7,
        ];
        $flag = $this->createFlag()['flag'];
        $booleanFlag = app(BooleanFlag::class);
        $booleanFlag->createVariant($flag, 'control', $attachment);

        $feature = app(Feature::class);
        $variants = $feature->findVariants($flag->getId());
        $this->assertEquals('on', collect($variants)->first()->getKey());
        $this->assertEmpty(collect($variants)->first()->getAttachment());
        $this->assertEquals('control', collect($variants)->last()->getKey());
        $this->assertEqualsCanonicalizing($attachment, collect($variants)->last()->getAttachment());
    }
}
