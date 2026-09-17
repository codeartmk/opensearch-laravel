<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\AvgBucket;
use PHPUnit\Framework\TestCase;

class AvgBucketTest extends TestCase
{
    public function testBuildsAAvgBucketAggregation()
    {
        $this->assertEquals([
            'avg_bucket' => ['buckets_path' => 'sales_per_month>sales'],
        ], AvgBucket::make('sales_per_month>sales')->toOpenSearchQuery());
    }

    public function testBuildsAAvgBucketAggregationWithAGapPolicy()
    {
        $this->assertEquals([
            'avg_bucket' => ['buckets_path' => 'sales_per_month>sales', 'gap_policy' => 'insert_zeros'],
        ], AvgBucket::make('sales_per_month>sales', 'insert_zeros')->toOpenSearchQuery());
    }
}
