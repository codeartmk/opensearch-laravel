<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\MaxBucket;
use PHPUnit\Framework\TestCase;

class MaxBucketTest extends TestCase
{
    public function testBuildsAMaxBucketAggregation()
    {
        $this->assertEquals([
            'max_bucket' => ['buckets_path' => 'sales_per_month>sales'],
        ], MaxBucket::make('sales_per_month>sales')->toOpenSearchQuery());
    }

    public function testBuildsAMaxBucketAggregationWithAGapPolicy()
    {
        $this->assertEquals([
            'max_bucket' => ['buckets_path' => 'sales_per_month>sales', 'gap_policy' => 'insert_zeros'],
        ], MaxBucket::make('sales_per_month>sales', 'insert_zeros')->toOpenSearchQuery());
    }
}
