<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `range` bucket aggregation: one bucket per numeric range. Ranges include `from` and exclude `to`. Not to be
 * confused with the range query.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/range/
 */
class Range implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The numeric field
     * @param array<int, array<string, mixed>> $ranges The ranges, each with `from` and/or `to` and an optional `key`,
     *                                                 e.g. [['to' => 50], ['from' => 50, 'to' => 100], ['from' => 100]]
     */
    public function __construct(
        private readonly string $field,
        private readonly array $ranges,
    ){}

    /**
     * @param string $field The numeric field
     * @param array<int, array<string, mixed>> $ranges The ranges, each with `from` and/or `to` and an optional `key`,
     *                                                 e.g. [['to' => 50], ['from' => 50, 'to' => 100], ['from' => 100]]
     * @return self
     */
    public static function make(string $field, array $ranges): self
    {
        return new self($field, $ranges);
    }

    /**
     * @return array{range: array{field: string, ranges: array<int, array<string, mixed>>}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'range' => [
                'field' => $this->field,
                'ranges' => $this->ranges
            ]
        ];
    }
}