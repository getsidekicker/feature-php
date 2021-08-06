<?php

namespace Sidekicker\FlagrFeature;

use Illuminate\Console\Command;

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

        if (!is_string($name) || !is_string($description) || !is_array($tags)) {
            $this->error('Please provide a valid name, description and tags');

            return Command::FAILURE;
        }

        $this->booleanFlag->createBooleanFlag(
            $name,
            $description,
            $tags
        );

        return Command::SUCCESS;
    }
}
