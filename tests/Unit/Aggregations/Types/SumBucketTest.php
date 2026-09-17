<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\SumBucket;
use PHPUnit\Framework\TestCase;

class SumBucketTest extends TestCase
{
    public function testBuildsASumBucketAggregation()
    {
        $this->assertEquals([
            'sum_bucket' => ['buckets_path' => 'sales_per_month>sales'],
        ], SumBucket::make('sales_per_month>sales')->toOpenSearchQuery());
    }

    public function testBuildsASumBucketAggregationWithAGapPolicy()
    {
        $this->assertEquals([
            'sum_bucket' => ['buckets_path' => 'sales_per_month>sales', 'gap_policy' => 'insert_zeros'],
        ], SumBucket::make('sales_per_month>sales', 'insert_zeros')->toOpenSearchQuery());
    }
}
