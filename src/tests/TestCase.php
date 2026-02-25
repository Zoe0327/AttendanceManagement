<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
