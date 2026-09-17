<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Range;
use PHPUnit\Framework\TestCase;

class RangeTest extends TestCase
{
    public function testBuildsARangeAggregation()
    {
        $ranges = [['to' => 10], ['from' => 10, 'to' => 20], ['from' => 20]];

        $this->assertEquals([
            'range' => [
                'field' => 'price',
                'ranges' => $ranges,
            ],
        ], Range::make('price', $ranges)->toOpenSearchQuery());
    }
}
