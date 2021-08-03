<?php

namespace Sidekicker\FlagrFeatureLaravel;

use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\Model\EvalContext;

class Feature
{
    public function __construct(private EvaluationApi $evaluator)
    {
    }

    /**
     * @param string $flag
     * @param array<mixed, mixed> $context
     * @return boolean
     */
    public function match(string $flag, array $context = []): bool
    {
        $match = false;

        $this->evaluate(
            $flag,
            $context,
            on: function (?object $attachment) use (&$match) {
                $match = true;
            }
        );

        return $match;
    }

    /**
     * @param string $flag
     * @param array<mixed, mixed> $context
     * @param callable ...$callbacks
     * @return void
     */
    public function evaluate(string $flag, array $context = [], callable ...$callbacks): void
    {
        $evalContext = new EvalContext();
        $evalContext->setFlagKey($flag);
        $evaluation = $this->evaluator->postEvaluation($evalContext);

        $callback = $callbacks[$evaluation->getVariantKey()]
            ?? $callbacks['otherwise']
            ?? fn (?object $attachment) => false;

        $callback($evaluation->getVariantAttachment());
    }
}
