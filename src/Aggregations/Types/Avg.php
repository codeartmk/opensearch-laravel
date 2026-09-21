<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * An `avg` metric aggregation: the average of a numeric field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/average/
 */
class Avg implements OpenSearchQuery, AggregationType
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
     * @return array{avg: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'avg' => [
                'field' => $this->field,
            ]
        ];
    }
}