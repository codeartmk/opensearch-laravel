<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `geo_bounds` metric aggregation: the bounding box that contains every value of a geo field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/geobounds/
 */
class GeoBounds implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The geo_point or geo_shape field
     */
    public function __construct(
        private readonly string $field,
    ){}

    /**
     * @param string $field The geo_point or geo_shape field
     * @return self
     */
    public static function make(string $field): self
    {
        return new self($field);
    }

    /**
     * @return array{geo_bounds: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'geo_bounds' => [
                'field' => $this->field,
            ]
        ];
    }
}
