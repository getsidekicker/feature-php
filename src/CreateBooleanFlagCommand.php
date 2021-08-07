<?php

namespace Sidekicker\FlagrFeature;

use Flagr\Client\ApiException;
use Illuminate\Console\Command;

//Create Laravel Command to create a new flag
class CreateBooleanFlagCommand extends Command
{
    protected $signature = 'feature:create-boolean-flag {--key=} {--description=} {[--tags=*]}';

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

        $this->info('Creating flag');

        if (!is_string($key) || !is_string($description) || !is_array($tags)) {
            $this->error('Please provide a valid key, description and tags');

            return Command::FAILURE;
        }

        try {
            $flag = $this->booleanFlag->createBooleanFlag(
                $key,
                $description,
                $tags
            );

            $this->info('flag ' . $flag->getKey() . '-' . $flag->getId() . ' created');
        } catch (ApiException $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
