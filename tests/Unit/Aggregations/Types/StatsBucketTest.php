<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\StatsBucket;
use PHPUnit\Framework\TestCase;

class StatsBucketTest extends TestCase
{
    public function testBuildsAStatsBucketAggregation()
    {
        $this->assertEquals([
            'stats_bucket' => ['buckets_path' => 'sales_per_month>sales'],
        ], StatsBucket::make('sales_per_month>sales')->toOpenSearchQuery());
    }

    public function testBuildsAStatsBucketAggregationWithAGapPolicy()
    {
        $this->assertEquals([
            'stats_bucket' => ['buckets_path' => 'sales_per_month>sales', 'gap_policy' => 'insert_zeros'],
        ], StatsBucket::make('sales_per_month>sales', 'insert_zeros')->toOpenSearchQuery());
    }
}
