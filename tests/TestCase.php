<?php

namespace Tests;

use App\Support\BaselineKeys;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // BaselineKeys caches the registered modules for the request; each test has its own database.
        BaselineKeys::flush();
    }
}
