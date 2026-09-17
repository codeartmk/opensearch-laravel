<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\DateRange;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    public function testBuildsADateRangeAggregation()
    {
        $ranges = [['to' => 'now-10M/M'], ['from' => 'now-10M/M']];

        $this->assertEquals([
            'date_range' => [
                'field' => 'created_at',
                'ranges' => $ranges,
            ],
        ], DateRange::make('created_at', $ranges)->toOpenSearchQuery());
    }

    public function testBuildsADateRangeAggregationWithAFormat()
    {
        $ranges = [['to' => '2026-02-01']];

        $this->assertEquals([
            'date_range' => [
                'field' => 'created_at',
                'ranges' => $ranges,
                'format' => 'yyyy-MM-dd',
            ],
        ], DateRange::make('created_at', $ranges, 'yyyy-MM-dd')->toOpenSearchQuery());
    }
}
