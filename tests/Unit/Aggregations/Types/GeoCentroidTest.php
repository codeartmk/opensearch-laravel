<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\GeoCentroid;
use PHPUnit\Framework\TestCase;

class GeoCentroidTest extends TestCase
{
    public function testBuildsAGeoCentroidAggregation()
    {
        $this->assertEquals([
            'geo_centroid' => ['field' => 'location'],
        ], GeoCentroid::make('location')->toOpenSearchQuery());
    }
}
