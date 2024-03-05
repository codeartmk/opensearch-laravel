<?php

namespace Codeart\OpensearchLaravel\Tests;

use Codeart\OpensearchLaravel\OpenSearchServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            OpenSearchServiceProvider::class,
        ];
    }
}