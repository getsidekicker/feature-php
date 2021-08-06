<?php

namespace Sidekicker\FlagrFeature;

use Illuminate\Console\Command;
use Flagr\Client\ApiException;

//Create Laravel Command to create a new flag
class CreateBooleanFlagCommand extends Command
{
    protected $signature = 'feature:create-boolean-flag {--name=} {--description=} {--tags=*}';

    protected $description = 'Create a new boolean flag within flagr';

    public function __construct(private BooleanFlag $booleanFlag)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->option('name');
        $description = $this->option('description');
        $tags = $this->option('tags');

        $this->info('Creating flag');

        if (!is_string($name) || !is_string($description) || !is_array($tags)) {
            $this->error('Please provide a valid name, description and tags');

            return Command::FAILURE;
        }

        try {
            $flag = $this->booleanFlag->createBooleanFlag(
                $name,
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
