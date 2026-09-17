<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class GeoDistance implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly float $lat,
        private readonly float $lon,
        private readonly string $distance
    ){}

    public static function make(string $field, float $lat, float $lon, string $distance): self
    {
        return new self($field, $lat, $lon, $distance);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'geo_distance' => [
                'distance' => $this->distance,
                $this->field => [
                    'lat' => $this->lat,
                    'lon' => $this->lon
                ]
            ]
        ];
    }
}
