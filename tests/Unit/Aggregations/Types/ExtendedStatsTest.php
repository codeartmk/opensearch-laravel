<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\ExtendedStats;
use PHPUnit\Framework\TestCase;

class ExtendedStatsTest extends TestCase
{
    public function testBuildsAnExtendedStatsAggregation()
    {
        $this->assertEquals([
            'extended_stats' => ['field' => 'price'],
        ], ExtendedStats::make('price')->toOpenSearchQuery());
    }

    public function testBuildsAnExtendedStatsAggregationWithSigma()
    {
        $this->assertEquals([
            'extended_stats' => ['field' => 'price', 'sigma' => 3],
        ], ExtendedStats::make('price', 3)->toOpenSearchQuery());
    }
}
