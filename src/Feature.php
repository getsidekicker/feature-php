<?php

namespace Sidekicker\FlagrFeature;

use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\ApiException;
use Flagr\Client\Model\EvalContext;

class Feature
{
    /**
     * @var array<mixed>
     */
    private array $globalContext = [];

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
     * @param array<mixed, mixed> $context
     *
     * @return boolean
     */
    public function match(string $flag, array $context = []): bool
    {
        $match = false;

        $this->evaluate(
            $flag,
            $context,
            on: function (?array $attachment) use (&$match) {
                $match = true;
            }
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
        $evalContext = new EvalContext();
        $evalContext->setFlagKey($flag);
        $evalContext->setEntityContext(array_merge($this->globalContext, $context));

        try {
            $evaluation = $this->evaluator->postEvaluation($evalContext);
            $variantKey = $evaluation->getVariantKey();
            $attachment = $evaluation->getVariantAttachment();
        } catch (ApiException $e) {
            $variantKey = 'error';
            $attachment = [];
        }

        $callback = $callbacks[$variantKey]
            ?? $callbacks['otherwise']
            ?? fn (?array $attachment) => false;

        $callback($attachment);
    }
}
