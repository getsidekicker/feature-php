<?php

namespace Sidekicker\Feature;

use Illuminate\Console\Command;

//Create Laravel Command to create a new flag
class CreateFlagCommand extends Command
{

    protected $signature = 'feature:create-flag {--name} {--description} {--tags=*}';

    protected $description = 'Create a new boolean flag within flagr';

    public function __construct(private CreateFlag $createFlag)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->createFlag->createFlag(
            $this->option('name'),
            $this->option('description'),
            $this->option('tags')
        );
    }
}
