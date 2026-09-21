<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Stats;
use PHPUnit\Framework\TestCase;

class StatsTest extends TestCase
{
    public function testBuildsAStatsAggregation()
    {
        $this->assertEquals([
            'stats' => ['field' => 'price'],
        ], Stats::make('price')->toOpenSearchQuery());
    }
}
