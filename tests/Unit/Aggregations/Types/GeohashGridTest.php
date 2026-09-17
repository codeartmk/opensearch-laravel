<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\GeohashGrid;
use PHPUnit\Framework\TestCase;

class GeohashGridTest extends TestCase
{
    public function testBuildsAGeohashGridAggregation()
    {
        $this->assertEquals([
            'geohash_grid' => ['field' => 'location'],
        ], GeohashGrid::make('location')->toOpenSearchQuery());
    }

    public function testBuildsAGeohashGridAggregationWithPrecisionAndSize()
    {
        $this->assertEquals([
            'geohash_grid' => ['field' => 'location', 'precision' => 4, 'size' => 100],
        ], GeohashGrid::make('location', 4, 100)->toOpenSearchQuery());
    }
}
