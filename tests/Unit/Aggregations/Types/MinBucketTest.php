<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\MinBucket;
use PHPUnit\Framework\TestCase;

class MinBucketTest extends TestCase
{
    public function testBuildsAMinBucketAggregation()
    {
        $this->assertEquals([
            'min_bucket' => ['buckets_path' => 'sales_per_month>sales'],
        ], MinBucket::make('sales_per_month>sales')->toOpenSearchQuery());
    }

    public function testBuildsAMinBucketAggregationWithAGapPolicy()
    {
        $this->assertEquals([
            'min_bucket' => ['buckets_path' => 'sales_per_month>sales', 'gap_policy' => 'insert_zeros'],
        ], MinBucket::make('sales_per_month>sales', 'insert_zeros')->toOpenSearchQuery());
    }
}
