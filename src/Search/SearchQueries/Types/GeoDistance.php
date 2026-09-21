<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `geo_distance` query: matches documents whose geopoint lies within the distance of a point.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/geo-and-xy/geodistance/
 */
class GeoDistance implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The geo_point field
     * @param float $lat The latitude of the centre
     * @param float $lon The longitude of the centre
     * @param string $distance The radius with its unit, e.g. '50km' or '10mi'
     */
    public function __construct(
        private readonly string $field,
        private readonly float $lat,
        private readonly float $lon,
        private readonly string $distance
    ){}

    /**
     * @param string $field The geo_point field
     * @param float $lat The latitude of the centre
     * @param float $lon The longitude of the centre
     * @param string $distance The radius with its unit, e.g. '50km' or '10mi'
     * @return self
     */
    public static function make(string $field, float $lat, float $lon, string $distance): self
    {
        return new self($field, $lat, $lon, $distance);
    }

    /**
     * @return array{geo_distance: array<string, string|array{lat: float, lon: float}>} Holds `distance` next to the field's point
     */
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
