<?php

use Sidekicker\FlagrFeatureLaravel\Feature;

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
     * @param array<mixed,mixed> $context
     * @return boolean
     */
    function feature_match(string $flag, array $context = []): bool
    {
        return app(Feature::class)->match($flag, $context);
    }
}
