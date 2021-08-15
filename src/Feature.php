<?php

namespace Sidekicker\FlagrFeature;

use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\ApiException;
use Flagr\Client\Model\EvaluationBatchRequest;

class Feature
{
    /**
     * @var array<mixed>
     */
    private array $globalContext = [];

    /**
     * @var array<mixed>
     */
    private array $evaluationResults = [];

    public function __construct(private EvaluationApi $evaluator)
    {
    }

    /**
     * @param array<mixed> $context
     * @return self
     */
    public function setGlobalContext(array $context): self
    {
        $this->globalContext = $context;

        return $this;
    }

    /**
     * @param string $flag
     * @param array<mixed> $context
     * @param array<mixed> $matchAttachment
     * @param string $matchVariant
     * @return boolean
     */
    public function match(string $flag, array $context = [], ?array &$matchAttachment = null, string $matchVariant = 'on'): bool
    {
        $match = false;
        $matchAttachment = null;

        $this->evaluate(
            $flag,
            $context,
            ...[$matchVariant => function (?array $attachment) use (&$match, &$matchAttachment) {
                $match = true;
                $matchAttachment = $attachment;
            }]
        );

        return $match;
    }

    /**
     * @param string $flag
     * @param array<mixed, mixed> $context
     * @param callable ...$callbacks
     *
     * @return void
     */
    public function evaluate(string $flag, array $context = [], callable ...$callbacks): void
    {
        [$variantKey, $attachment] = $this->performEvaluation($flag, $context);

        $callback = $callbacks[$variantKey]
            ?? $callbacks['otherwise']
            ?? fn (?array $attachment) => false;

        $callback($attachment);
    }

    /**
     * @param string $flag
     * @param array<mixed> $context
     * @return array<mixed>
     */
    private function performEvaluation(string $flag, array $context): array
    {
        if (!isset($this->evaluationResults[$flag])) {
            $this->evaluationResults = [];
            $evaluationBatchRequest = new EvaluationBatchRequest();
            if (is_array(config('flagr-feature.tags')) && count(config('flagr-feature.tags')) > 0) {
                $evaluationBatchRequest->setFlagTags(config('flagr-feature.tags'));
                $evaluationBatchRequest->setFlagTagsOperator(config('flagr-feature.tag_operator'));
            } else {
                $evaluationBatchRequest->setFlagKeys([$flag]);
            }
            $evaluationBatchRequest->setEntities([array_merge($this->globalContext, $context)]);

            try {

                $results = $this->evaluator->postEvaluationBatch($evaluationBatchRequest)->getEvaluationResults() ?? [];

                foreach ($results as $evaluationResult) {
                    $this->evaluationResults[$evaluationResult->getFlagKey()] = [
                        $evaluationResult->getVariantKey(),
                        $evaluationResult->getVariantAttachment()
                    ];
                }
            } catch (ApiException $e) {
                //
            }
        }

        // Only attempt to evaluate a flag once per instantiation
        $this->evaluationResults[$flag] = $this->evaluationResults[$flag] ?? ['', null];

        return $this->evaluationResults[$flag];
    }
}
