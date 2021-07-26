<?php

use Sidekicker\Feature\Feature;

function feature_eval(string $flag, array $context = [], callable ...$callbacks): void
{
    app(Feature::class)->evaluate($flag, $context, ...$callbacks);
}

function feature_match(string $flag, array $context = []): bool
{
    return app(Feature::class)->match($flag, $context);
}
