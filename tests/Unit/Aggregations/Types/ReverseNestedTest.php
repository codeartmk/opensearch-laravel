<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\ReverseNested;
use PHPUnit\Framework\TestCase;

class ReverseNestedTest extends TestCase
{
    public function testBuildsAReverseNestedAggregationToTheRoot()
    {
        $this->assertSame('{"reverse_nested":{}}', json_encode(ReverseNested::make()->toOpenSearchQuery()));
    }

    public function testBuildsAReverseNestedAggregationWithAPath()
    {
        $this->assertEquals([
            'reverse_nested' => ['path' => 'comments'],
        ], ReverseNested::make('comments')->toOpenSearchQuery());
    }
}
