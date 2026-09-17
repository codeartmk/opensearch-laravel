<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Derivative;
use PHPUnit\Framework\TestCase;

class DerivativeTest extends TestCase
{
    public function testBuildsADerivativeAggregation()
    {
        $this->assertEquals([
            'derivative' => ['buckets_path' => 'sales_per_month>sales'],
        ], Derivative::make('sales_per_month>sales')->toOpenSearchQuery());
    }

    public function testBuildsADerivativeAggregationWithAGapPolicy()
    {
        $this->assertEquals([
            'derivative' => ['buckets_path' => 'sales_per_month>sales', 'gap_policy' => 'insert_zeros'],
        ], Derivative::make('sales_per_month>sales', 'insert_zeros')->toOpenSearchQuery());
    }
}
