<?php

namespace Frkcn\Kasiyer\Tests;

use Frkcn\Kasiyer\KasiyerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [KasiyerServiceProvider::class];
    }
}
