<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\BucketSelector;
use PHPUnit\Framework\TestCase;

class BucketSelectorTest extends TestCase
{
    public function testBuildsABucketSelectorAggregation()
    {
        $this->assertEquals([
            'bucket_selector' => [
                'buckets_path' => ['total' => 'total_sales'],
                'script' => 'params.total > 100',
            ],
        ], BucketSelector::make(['total' => 'total_sales'], 'params.total > 100')->toOpenSearchQuery());
    }

    public function testBuildsABucketSelectorAggregationWithAGapPolicy()
    {
        $this->assertEquals([
            'bucket_selector' => [
                'buckets_path' => ['total' => 'total_sales'],
                'script' => 'params.total > 100',
                'gap_policy' => 'skip',
            ],
        ], BucketSelector::make(['total' => 'total_sales'], 'params.total > 100', 'skip')->toOpenSearchQuery());
    }
}
