<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `cardinality` metric aggregation: the approximate number of distinct values of a field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/cardinality/
 */
class Cardinality implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The field whose distinct values are counted
     */
    public function __construct(
        private readonly string $field,
    ){}

    /**
     * @param string $field The field whose distinct values are counted
     * @return self
     */
    public static function make(string $field): self
    {
        return new self($field);
    }

    /**
     * @return array{cardinality: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'cardinality' => [
                'field' => $this->field,
            ]
        ];
    }
}