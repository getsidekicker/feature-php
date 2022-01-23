<?php

namespace Sidekicker\FlagrFeature;

use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\ApiException;
use Flagr\Client\Model\EvaluationBatchRequest;
use Flagr\Client\Model\EvaluationEntity;
use Illuminate\Config\Repository;

class Feature
{
    /**
     * @var string
     */
    private ?string $id = null;

    /**
     * @var array<mixed>
     */
    private array $context = [];

    /**
     * @var array<mixed>
     */
    private array $evaluationResults = [];

    public function __construct(private EvaluationApi $evaluator, private Repository $config)
    {
        $this->id = $this->config->get('flagr-feature.id');
    }

    /**
     * @param string $id
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->clear();
        $this->id = $id;

        return $this;
    }

    /**
     * @param array<mixed> $context
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->clear();
        $this->context = $context;

        return $this;
    }


    /**
     * @param array<mixed> $context
     * @return self
     */
    public function addContext(array $context): self
    {
        $this->setContext(array_merge($this->context, $context));

        return $this;
    }

    /**
     * @param string $flag
     * @param array<mixed> $matchAttachment
     * @param string $matchVariant
     * @return boolean
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
     * @param string $flag
     * @param callable ...$callbacks
     *
     * @return void
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
     * Clear internal cache
     */
    private function clear(): void
    {
        $this->evaluationResults = [];
    }

    /**
     * @param string $flag
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
                if ($response instanceof  \Flagr\Client\Model\EvaluationBatchResponse) {
                    $results = $response->getEvaluationResults() ?? [];

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
