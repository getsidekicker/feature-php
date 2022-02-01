<?php

namespace Sidekicker\FlagrFeature;

use Flagr\Client\ApiException;
use Illuminate\Console\Command;

//Create Laravel Command to create a new flag
class CreateBooleanFlagCommand extends Command
{
    protected $signature = 'feature:create-boolean-flag {--key=} {--description=} [{--tags=*}] [{--percentage=100}]';

    protected $description = 'Create a new boolean flag within flagr';

    public function __construct(private BooleanFlag $booleanFlag)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $key = $this->option('key');
        $description = $this->option('description');
        $tags = $this->option('tags');
        $percentage = (int) $this->option('percentage');

        $this->info('Creating flag');

        if (!is_string($key) || !is_string($description) || !is_array($tags) || !($percentage >= 0 && $percentage <= 100)) {
            $this->error('Please provide a valid key, description, tags and percentage');

            return Command::FAILURE;
        }

        try {
            $flag = $this->booleanFlag->createBooleanFlag(
                $key,
                $description,
                $tags,
                $percentage
            );

            $this->info('flag ' . $flag->getKey() . '-' . $flag->getId() . ' created');
        } catch (ApiException $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
