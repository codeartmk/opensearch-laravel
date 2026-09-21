<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `sum` metric aggregation: the total of a numeric field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/sum/
 */
class Sum implements OpenSearchQuery, AggregationType
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
     * @return array{sum: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'sum' => [
                'field' => $this->field,
            ]
        ];
    }
}