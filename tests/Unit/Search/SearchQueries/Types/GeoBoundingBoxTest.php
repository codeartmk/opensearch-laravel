<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\GeoBoundingBox;
use PHPUnit\Framework\TestCase;

class GeoBoundingBoxTest extends TestCase
{
    public function testBuildsAGeoBoundingBoxQueryFromCoordinates()
    {
        $query = GeoBoundingBox::make('location', ['lat' => 42.5, 'lon' => 20.5], ['lat' => 41.5, 'lon' => 21.5]);

        $this->assertEquals([
            'geo_bounding_box' => [
                'location' => [
                    'top_left' => ['lat' => 42.5, 'lon' => 20.5],
                    'bottom_right' => ['lat' => 41.5, 'lon' => 21.5],
                ],
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testBuildsAGeoBoundingBoxQueryFromGeohashes()
    {
        $this->assertEquals([
            'geo_bounding_box' => [
                'location' => [
                    'top_left' => 'srxq',
                    'bottom_right' => 'srx5',
                ],
            ],
        ], GeoBoundingBox::make('location', 'srxq', 'srx5')->toOpenSearchQuery());
    }
}
