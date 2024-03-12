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
        $this->assertEquals('on', $variants[0]->getKey());
        $this->assertEmpty($variants[0]->getAttachment());
        $this->assertEquals('control', $variants[1]->getKey());
        $this->assertEqualsCanonicalizing($attachment, $variants[1]->getAttachment());
    }
}
