<?php

namespace Sidekicker\FlagrFeature;

use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\Api\VariantApi;
use Flagr\Client\ApiException;
use Flagr\Client\Model\Error;
use Flagr\Client\Model\EvaluationBatchRequest;
use Flagr\Client\Model\EvaluationEntity;
use Flagr\Client\Model\Variant;
use Illuminate\Config\Repository;

class Feature
{
    private ?string $id = null;

    /**
     * @var array<mixed>
     */
    private array $context = [];

    /**
     * @var array<mixed>
     */
    private array $evaluationResults = [];

    /**
     * @var array<int, Variant[]|null>
     */
    private array $variantResults = [];

    public function __construct(
        private EvaluationApi $evaluator,
        private VariantApi $variant,
        private Repository $config
    ) {
        $this->id = $this->config->get('flagr-feature.id');
    }

    public function setId(?string $id): self
    {
        $this->clear();
        $this->id = $id;

        return $this;
    }

    /**
     * @param array<mixed> $context
     */
    public function setContext(array $context): self
    {
        $this->clear();
        $this->context = $context;

        return $this;
    }

    /**
     * @param array<mixed> $context
     */
    public function addContext(array $context): self
    {
        $this->setContext(array_merge($this->context, $context));

        return $this;
    }

    /**
     * @param array<mixed> $matchAttachment
     */
    public function match(string $flag, ?array &$matchAttachment = null, string $matchVariant = 'on'): bool
    {
        $match = false;
        $matchAttachment = null;

        $this->evaluate(
            $flag,
            ...[$matchVariant => function (?array $attachment) use (&$match, &$matchAttachment) {
                $match = true;
                $matchAttachment = $attachment;
            }]
        );

        return $match;
    }

    /**
     * @param callable ...$callbacks
     *
     */
    public function evaluate(string $flag, callable ...$callbacks): void
    {
        [$variantKey, $attachment] = $this->performEvaluation($flag);

        $callback = $callbacks[$variantKey]
            ?? $callbacks['otherwise']
            ?? fn (?array $attachment) => false;

        $callback($attachment);
    }

    /**
     *
     * @return array{flag: string, key: string, attachment: array<mixed>}
     */
    public function variant(string $flag): ?array
    {
        [$variantKey, $attachment] = $this->performEvaluation($flag);

        return $variantKey
            ? ['flag' => $flag, 'key' => $variantKey, 'attachment' => $attachment]
            : null;
    }

    /**
     * @return Variant[] | null
     */
    public function findVariants(int $flagId): ?array
    {
        if (!isset($this->variantResults[$flagId])) {
            try {
                $response = $this->variant->findVariants($flagId);
                if (!$response instanceof Error) {
                    $this->variantResults[$flagId] = $response;
                }
            } catch (ApiException $e) {
            }
        }

        return $this->variantResults[$flagId] ?? null;
    }

    /**
     * Clear internal cache
     */
    private function clear(): void
    {
        $this->evaluationResults = [];
    }

    /**
     * @return array<mixed>
     */
    private function performEvaluation(string $flag): array
    {
        if (!isset($this->evaluationResults[$flag])) {
            $this->evaluationResults = [];
            $evaluationBatchRequest = new EvaluationBatchRequest();
            if (is_array($this->config->get('flagr-feature.tags')) && count($this->config->get('flagr-feature.tags')) > 0) {
                $evaluationBatchRequest->setFlagTags($this->config->get('flagr-feature.tags', null));
                $evaluationBatchRequest->setFlagTagsOperator($this->config->get('flagr-feature.tag_operator', 'ANY'));
            } else {
                $evaluationBatchRequest->setFlagKeys([$flag]);
            }
            $evaluationBatchRequest->setEntities([
                new EvaluationEntity([
                    'entity_id' => $this->id,
                    'entity_context' => $this->context
                ])
            ]);

            try {
                $response = $this->evaluator->postEvaluationBatch($evaluationBatchRequest);
                if ($response instanceof \Flagr\Client\Model\EvaluationBatchResponse) {
                    $results = $response->getEvaluationResults() ?: [];

                    foreach ($results as $evaluationResult) {
                        $this->evaluationResults[$evaluationResult->getFlagKey()] = [
                            $evaluationResult->getVariantKey(),
                            $evaluationResult->getVariantAttachment()
                        ];
                    }
                }
            } catch (ApiException $e) {
            }
        }

        // Only attempt to evaluate a flag once per instantiation
        $this->evaluationResults[$flag] = $this->evaluationResults[$flag] ?? ['', null];

        return $this->evaluationResults[$flag];
    }
}
