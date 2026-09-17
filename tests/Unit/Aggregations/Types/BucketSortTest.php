<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\BucketSort;
use PHPUnit\Framework\TestCase;

class BucketSortTest extends TestCase
{
    public function testBuildsABucketSortAggregation()
    {
        $this->assertEquals([
            'bucket_sort' => [
                'sort' => ['_key'],
            ],
        ], BucketSort::make('_key')->toOpenSearchQuery());
    }

    public function testBuildsABucketSortAggregationWithOrderSizeAndFrom()
    {
        $this->assertEquals([
            'bucket_sort' => [
                'sort' => [
                    ['total_sales' => ['order' => 'desc']],
                ],
                'size' => 5,
                'from' => 10,
            ],
        ], BucketSort::make('total_sales', 'desc', 5, 10)->toOpenSearchQuery());
    }
}
