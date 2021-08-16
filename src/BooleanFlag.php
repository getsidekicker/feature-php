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
use Flagr\Client\Model\Error;
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
     * @param array<string> $tags
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
        if ($flag instanceof Error) {
            throw new FlagrFeatureException($flag->getMessage());
        }

        $variant =  $this->createVariant($flag);
        if ($variant instanceof Error) {
            throw new FlagrFeatureException($variant->getMessage());
        }

        $this->addTags($flag, $tags);
        $this->createSegment(
            $flag,
            $variant,
            'Feature Enabled'
        );

        return $flag;
    }

    /**
     * @param Flag $flag
     * @param array<mixed> $tags
     * @return \Flagr\Client\Model\Tag[]|\Flagr\Client\Model\Error[]
     */
    private function addTags(Flag $flag, array $tags): array
    {
        return array_map(
            fn ($tag) => $this->tagApi->createTag(
                (int) $flag->getId(),
                new CreateTagRequest(['value' => $tag])
            ),
            $tags
        );
    }

    private function createVariant(Flag $flag): Variant|Error
    {
        return $this->variantApi->createVariant(
            (int) $flag->getId(),
            new CreateVariantRequest([
                'key' => 'on'
            ])
        );
    }

    private function createSegment(Flag $flag, Variant $variant, string $description, int $rollout = 100): Segment
    {
        $segment = $this->segmentApi->createSegment(
            (int) $flag->getId(),
            new CreateSegmentRequest([
                'description' => $description,
                'rollout_percent' => $rollout,
            ])
        );

        if ($segment instanceof Error) {
            throw new FlagrFeatureException($segment->getMessage());
        }

        $this->distributionApi->putDistributions(
            (int) $flag->getId(),
            (int) $segment->getId(),
            new PutDistributionsRequest([
                'distributions' => [
                    new Distribution([
                        'percent' => 100,
                        'variant_id' => (int) $variant->getId(),
                        'variant_key' => $variant->getKey()
                    ])
                ]
            ])
        );

        return $segment;
    }
}
