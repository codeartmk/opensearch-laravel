<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\GeoDistance;
use PHPUnit\Framework\TestCase;

class GeoDistanceTest extends TestCase
{
    public function testBuildsAGeoDistanceQuery()
    {
        $this->assertEquals([
            'geo_distance' => [
                'distance' => '50km',
                'location' => [
                    'lat' => 41.99,
                    'lon' => 21.43,
                ],
            ],
        ], GeoDistance::make('location', 41.99, 21.43, '50km')->toOpenSearchQuery());
    }
}
