<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\GeoDistance;
use PHPUnit\Framework\TestCase;

class GeoDistanceTest extends TestCase
{
    public function testBuildsAGeoDistanceAggregation()
    {
        $ranges = [['to' => 100], ['from' => 100]];

        $this->assertEquals([
            'geo_distance' => [
                'field' => 'location',
                'origin' => ['lat' => 41.99, 'lon' => 21.43],
                'ranges' => $ranges,
            ],
        ], GeoDistance::make('location', 41.99, 21.43, $ranges)->toOpenSearchQuery());
    }

    public function testBuildsAGeoDistanceAggregationWithAUnit()
    {
        $ranges = [['to' => 100]];

        $this->assertEquals([
            'geo_distance' => [
                'field' => 'location',
                'origin' => ['lat' => 41.99, 'lon' => 21.43],
                'ranges' => $ranges,
                'unit' => 'km',
            ],
        ], GeoDistance::make('location', 41.99, 21.43, $ranges, 'km')->toOpenSearchQuery());
    }
}
