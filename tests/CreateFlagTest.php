<?php

namespace Sidekicker\Feature\Tests;

use Sidekicker\Feature\CreateFlag;

// Test class that will test the CreateFlag class
class CreateFlagTest extends TestCase
{

    // Test that the CreateFlag class can be instantiated
    public function testCreateFlag()
    {
        $flag = app(CreateFlag::class);
        $this->assertInstanceOf(CreateFlag::class, $flag);
    }

    // Test calling of the command
    public function testCreateFlagCommand()
    {
        $this->artisan('feature:create-flag', ['--name' => uniqid('test-flag'), '--description' => 'This is a test flag'])->assertExitCode(0);
    }
}
