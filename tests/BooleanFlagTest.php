<?php

namespace Sidekicker\FlagrFeature\Tests;

use Illuminate\Testing\PendingCommand;
use Sidekicker\FlagrFeature\BooleanFlag;

// Test class that will test the CreateFlag class
class BooleanFlagTest extends TestCase
{
    public function testInstantiateBooleanFlag(): void
    {
        $flag = app(BooleanFlag::class);
        $this->assertInstanceOf(BooleanFlag::class, $flag);
    }

    public function testCreateBooleanFlagCommand(): void
    {
        $return = $this->artisan('feature:create-boolean-flag', ['--key' => uniqid('test-flag'), '--description' => 'This is a test flag']);
        $this->assertInstanceOf(PendingCommand::class, $return);
        $return->assertExitCode(0);
    }
}
