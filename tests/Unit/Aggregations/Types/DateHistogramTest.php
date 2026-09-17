<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\DateHistogram;
use PHPUnit\Framework\TestCase;

class DateHistogramTest extends TestCase
{
    public function testBuildsADateHistogramWithACalendarInterval()
    {
        $this->assertEquals([
            'date_histogram' => [
                'field' => 'created_at',
                'calendar_interval' => 'month',
            ],
        ], DateHistogram::make('created_at', 'month')->toOpenSearchQuery());
    }

    public function testBuildsADateHistogramWithAFixedIntervalFormatAndOffset()
    {
        $this->assertEquals([
            'date_histogram' => [
                'field' => 'created_at',
                'fixed_interval' => '30d',
                'offset' => '+6h',
                'format' => 'yyyy-MM-dd',
            ],
        ], DateHistogram::make('created_at', '30d', true, 'yyyy-MM-dd', '+6h')->toOpenSearchQuery());
    }
}
