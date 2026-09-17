<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\GeoBounds;
use PHPUnit\Framework\TestCase;

class GeoBoundsTest extends TestCase
{
    public function testBuildsAGeoBoundsAggregation()
    {
        $this->assertEquals([
            'geo_bounds' => ['field' => 'location'],
        ], GeoBounds::make('location')->toOpenSearchQuery());
    }
}
