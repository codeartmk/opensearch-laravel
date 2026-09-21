<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `stats` metric aggregation: the count, min, max, avg and sum of a numeric field in one aggregation.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/stats/
 */
class Stats implements OpenSearchQuery, AggregationType
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
     * @return array{stats: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'stats' => [
                'field' => $this->field,
            ]
        ];
    }
}