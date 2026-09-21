<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Range;
use PHPUnit\Framework\TestCase;

class RangeTest extends TestCase
{
    public function testBuildsARangeQuery()
    {
        $this->assertEquals([
            'range' => [
                'price' => ['gte' => 10, 'lt' => 20],
            ],
        ], Range::make('price', ['gte' => 10, 'lt' => 20])->toOpenSearchQuery());
    }

    public function testPassesDateRangeOptionsThroughAsIs()
    {
        $this->assertEquals([
            'range' => [
                'created_at' => [
                    'gte' => 'now-1d/d',
                    'lt' => 'now/d',
                    'format' => 'strict_date_optional_time',
                    'time_zone' => '+01:00',
                ],
            ],
        ], Range::make('created_at', [
            'gte' => 'now-1d/d',
            'lt' => 'now/d',
            'format' => 'strict_date_optional_time',
            'time_zone' => '+01:00',
        ])->toOpenSearchQuery());
    }
}
