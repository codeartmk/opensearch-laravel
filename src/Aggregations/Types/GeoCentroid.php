<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `geo_centroid` metric aggregation: the weighted centre of every value of a geo field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/geocentroid/
 */
class GeoCentroid implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The geo_point field
     */
    public function __construct(
        private readonly string $field,
    ){}

    /**
     * @param string $field The geo_point field
     * @return self
     */
    public static function make(string $field): self
    {
        return new self($field);
    }

    /**
     * @return array{geo_centroid: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'geo_centroid' => [
                'field' => $this->field,
            ]
        ];
    }
}
