<?php

namespace Sidekicker\FlagrFeatureLaravel\Tests;

use Illuminate\Testing\PendingCommand;
use Sidekicker\FlagrFeatureLaravel\CreateFlag;

// Test class that will test the CreateFlag class
class CreateFlagTest extends TestCase
{
    // Test that the CreateFlag class can be instantiated
    public function testCreateFlag(): void
    {
        $flag = app(CreateFlag::class);
        $this->assertInstanceOf(CreateFlag::class, $flag);
    }

    // Test calling of the command
    public function testCreateFlagCommand(): void
    {
        $return = $this->artisan('feature:create-flag', ['--name' => uniqid('test-flag'), '--description' => 'This is a test flag']);
        $this->assertInstanceOf(PendingCommand::class, $return);
        //@phpstan-ignore-next-line
        $return->assertExitCode(0);
    }
}
