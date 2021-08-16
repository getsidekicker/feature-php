<?php

use Sidekicker\FlagrFeature\Feature;

if (!function_exists('feature_eval')) {
    /**
     * @param string $flag
     * @param array<mixed, mixed> $context
     * @param callable ...$callbacks
     * @return void
     */
    function feature_eval(string $flag, array $context = [], callable ...$callbacks): void
    {
        app(Feature::class)->evaluate($flag, $context, ...$callbacks);
    }
}

if (!function_exists('feature_match')) {
    /**
     * @param string $flag
     * @param array<mixed> $context
     * @param array<mixed>|null $matchAttachment
     * @param string $matchVariant
     * @return boolean
     */
    function feature_match(string $flag, array $context = [], ?array &$matchAttachment = null, string $matchVariant = 'on'): bool
    {
        return app(Feature::class)->match($flag, $context, $matchAttachment, $matchVariant);
    }
}


if (!function_exists('feature_add_context')) {
    /**
     * @param array<mixed> $context
     * @return void
     */
    function feature_add_context(array $context = []): void
    {
        app(Feature::class)->addContext($context);
    }
}
