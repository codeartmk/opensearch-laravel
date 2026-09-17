<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class GeoBoundingBox implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param array|string $topLeft Any geopoint format, e.g. ['lat' => 42.1, 'lon' => 20.4] or a geohash
     * @param array|string $bottomRight Any geopoint format, e.g. ['lat' => 40.8, 'lon' => 23.1] or a geohash
     */
    public function __construct(
        private readonly string $field,
        private readonly array|string $topLeft,
        private readonly array|string $bottomRight
    ){}

    public static function make(string $field, array|string $topLeft, array|string $bottomRight): self
    {
        return new self($field, $topLeft, $bottomRight);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'geo_bounding_box' => [
                $this->field => [
                    'top_left' => $this->topLeft,
                    'bottom_right' => $this->bottomRight
                ]
            ]
        ];
    }
}
