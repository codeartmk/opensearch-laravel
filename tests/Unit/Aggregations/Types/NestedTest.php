<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Nested;
use PHPUnit\Framework\TestCase;

class NestedTest extends TestCase
{
    public function testBuildsANestedAggregation()
    {
        $this->assertEquals([
            'nested' => ['path' => 'comments'],
        ], Nested::make('comments')->toOpenSearchQuery());
    }
}
