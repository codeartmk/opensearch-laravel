<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\CumulativeSum;
use PHPUnit\Framework\TestCase;

class CumulativeSumTest extends TestCase
{
    public function testBuildsACumulativeSumAggregation()
    {
        $this->assertEquals([
            'cumulative_sum' => ['buckets_path' => 'sales'],
        ], CumulativeSum::make('sales')->toOpenSearchQuery());
    }

    public function testBuildsACumulativeSumAggregationWithAFormat()
    {
        $this->assertEquals([
            'cumulative_sum' => ['buckets_path' => 'sales', 'format' => '0.00'],
        ], CumulativeSum::make('sales', '0.00')->toOpenSearchQuery());
    }
}
