<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\TopHits;
use PHPUnit\Framework\TestCase;

class TopHitsTest extends TestCase
{
    public function testBuildsATopHitsAggregationWithTheDefaultSize()
    {
        $this->assertEquals([
            'top_hits' => ['size' => 3],
        ], TopHits::make()->toOpenSearchQuery());
    }

    public function testBuildsATopHitsAggregationWithSortSourceAndFrom()
    {
        $this->assertEquals([
            'top_hits' => [
                'size' => 1,
                'sort' => [['price' => ['order' => 'desc']]],
                '_source' => ['title', 'price'],
                'from' => 2,
            ],
        ], TopHits::make(1, [['price' => ['order' => 'desc']]], ['title', 'price'], 2)->toOpenSearchQuery());
    }

    public function testCanLeaveOutTheSource()
    {
        $this->assertEquals([
            'top_hits' => ['size' => 3, '_source' => false],
        ], TopHits::make(source: false)->toOpenSearchQuery());
    }
}
