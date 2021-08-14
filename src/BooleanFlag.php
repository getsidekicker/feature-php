<?php

namespace Sidekicker\FlagrFeature;

use Flagr\Client\Api\DistributionApi;
use Flagr\Client\Api\FlagApi;
use Flagr\Client\Api\SegmentApi;
use Flagr\Client\Api\TagApi;
use Flagr\Client\Api\VariantApi;
use Flagr\Client\Model\CreateFlagRequest;
use Flagr\Client\Model\CreateSegmentRequest;
use Flagr\Client\Model\CreateTagRequest;
use Flagr\Client\Model\CreateVariantRequest;
use Flagr\Client\Model\Distribution;
use Flagr\Client\Model\Flag;
use Flagr\Client\Model\PutDistributionsRequest;
use Flagr\Client\Model\Segment;
use Flagr\Client\Model\Variant;

class BooleanFlag
{
    public function __construct(
        private FlagApi $flagApi,
        private TagApi $tagApi,
        private SegmentApi $segmentApi,
        private VariantApi $variantApi,
        private DistributionApi $distributionApi
    ) {
    }

    /**
     * @param string $key
     * @param string $description
     * @param array<string, string> $tags
     *
     * @throws \Flagr\Client\ApiException
     *
     * @return Flag
     */
    public function createBooleanFlag(string $key, string $description, array $tags = []): Flag
    {
        $body = new CreateFlagRequest();

        $body->setKey($key);
        $body->setDescription($description);

        $flag = $this->flagApi->createFlag($body);

        $this->addTags($flag, $tags);
        $this->createSegment(
            $flag,
            $this->createVariant($flag),
            'Feature Enabled'
        );

        return $flag;
    }

    /**
     * @param Flag $flag
     * @param array $tags
     * @return Tag[]
     */
    private function addTags(Flag $flag, array $tags): array
    {
        return array_map(
            fn ($tag) => $this->tagApi->createTag(
                $flag->getId(),
                new CreateTagRequest(['value' => $tag])
            ),
            $tags
        );
    }

    private function createVariant(Flag $flag): Variant
    {
        return $this->variantApi->createVariant(
            $flag->getId(),
            new CreateVariantRequest([
                'key' => 'on'
            ])
        );
    }

    private function createSegment(Flag $flag, Variant $variant, string $description, int $rollout = 100): Segment
    {
        $segment = $this->segmentApi->createSegment(
            $flag->getId(),
            new CreateSegmentRequest([
                'description' => $description,
                'rollout_percent' => $rollout,
            ])
        );

        $this->distributionApi->putDistributions(
            $flag->getId(),
            $segment->getId(),
            new PutDistributionsRequest([
                'distributions' => [
                    new Distribution([
                        'percent' => 100,
                        'variant_id' => $variant->getId(),
                        'variant_key' => $variant->getKey()
                    ])
                ]
            ])
        );

        return $segment;
    }
}
