<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `percentile_ranks` metric aggregation: the percentage of the documents whose numeric field is at or below each
 * of the given values.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/percentile-ranks/
 */
class PercentileRanks implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The numeric field
     * @param array<int, int|float> $values The values to rank, e.g. [50, 100]
     */
    public function __construct(
        private readonly string $field,
        private readonly array $values
    ){}

    /**
     * @param string $field The numeric field
     * @param array<int, int|float> $values The values to rank, e.g. [50, 100]
     * @return self
     */
    public static function make(string $field, array $values): self
    {
        return new self($field, $values);
    }

    /**
     * @return array{percentile_ranks: array{field: string, values: array<int, int|float>}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'percentile_ranks' => [
                'field' => $this->field,
                'values' => $this->values,
            ]
        ];
    }
}
