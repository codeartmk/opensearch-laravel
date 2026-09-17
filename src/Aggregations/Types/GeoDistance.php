<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class GeoDistance implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly float $lat,
        private readonly float $lon,
        private readonly array $ranges,
        private readonly ?string $unit
    ){}

    public static function make(string $field, float $lat, float $lon, array $ranges, ?string $unit = null): self
    {
        return new self($field, $lat, $lon, $ranges, $unit);
    }

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
