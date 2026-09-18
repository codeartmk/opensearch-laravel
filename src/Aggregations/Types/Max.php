<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `max` metric aggregation: the largest value of a numeric field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/maximum/
 */
class Max implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The numeric field
     */
    public function __construct(
        private readonly string $field,
    ){}

    /**
     * @param string $field The numeric field
     * @return self
     */
    public static function make(string $field): self
    {
        return new self($field);
    }

    /**
     * @return array{max: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'max' => [
                'field' => $this->field,
            ]
        ];
    }
}