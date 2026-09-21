<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `geo_distance` bucket aggregation: buckets documents by their distance from a point into rings. Not to be
 * confused with the geo_distance query.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/geo-distance/
 */
class GeoDistance implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The geo_point field
     * @param float $lat The latitude of the origin
     * @param float $lon The longitude of the origin
     * @param array<int, array<string, mixed>> $ranges The rings, each with `from` and/or `to` in $unit, e.g. [['to' =>
     *                                                 100], ['from' => 100, 'to' => 500]]
     * @param string|null $unit The distance unit of the ranges, e.g. 'km' or 'mi'. Null leaves it out so OpenSearch's
     *                          default (m) applies
     */
    public function __construct(
        private readonly string $field,
        private readonly float $lat,
        private readonly float $lon,
        private readonly array $ranges,
        private readonly ?string $unit
    ){}

    /**
     * @param string $field The geo_point field
     * @param float $lat The latitude of the origin
     * @param float $lon The longitude of the origin
     * @param array<int, array<string, mixed>> $ranges The rings, each with `from` and/or `to` in $unit, e.g. [['to' =>
     *                                                 100], ['from' => 100, 'to' => 500]]
     * @param string|null $unit The distance unit of the ranges, e.g. 'km' or 'mi'. Null leaves it out so OpenSearch's
     *                          default (m) applies
     * @return self
     */
    public static function make(string $field, float $lat, float $lon, array $ranges, ?string $unit = null): self
    {
        return new self($field, $lat, $lon, $ranges, $unit);
    }

    /**
     * @return array{geo_distance: array{field: string, origin: array{lat: float, lon: float}, ranges: array<int, array<string, mixed>>, unit?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'geo_distance' => [
                'field' => $this->field,
                'origin' => [
                    'lat' => $this->lat,
                    'lon' => $this->lon,
                ],
                'ranges' => $this->ranges,
            ]
        ];

        if (!is_null($this->unit)) {
            $query['geo_distance']['unit'] = $this->unit;
        }

        return $query;
    }
}
