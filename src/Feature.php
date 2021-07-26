<?php

namespace Sidekicker\Feature;

use Flagr\Client\Api\EvaluationApi;
use Flagr\Client\Model\EvalContext;

class Feature
{
    public function __construct(private EvaluationApi $evaluator)
    {
    }

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

    public function evaluate(string $flag, array $context = [], callable ...$callbacks): void
    {
        $evalContext = new EvalContext();
        $evalContext->setFlagKey($flag);
        $evalContext->setEnableDebug(true);
        $evaluation = $this->evaluator->postEvaluation($evalContext);

        $callback = $callbacks[$evaluation->getVariantKey()]
            ?? $callbacks['_']
            ?? fn (?array $attachment) => false;

        $callback($evaluation->getVariantAttachment());
    }
}
