<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Exists;
use PHPUnit\Framework\TestCase;

class ExistsTest extends TestCase
{
    public function testBuildsAnExistsQuery()
    {
        $this->assertEquals([
            'exists' => ['field' => 'email'],
        ], Exists::make('email')->toOpenSearchQuery());
    }
}
