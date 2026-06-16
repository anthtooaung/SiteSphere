<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvTest extends TestCase
{
    public function test_env()
    {
        echo 'APP_ENV='.app()->environment()."\n";
        $this->assertTrue(true);
    }
}
