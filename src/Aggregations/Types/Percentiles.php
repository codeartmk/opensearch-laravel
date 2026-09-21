<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `percentiles` metric aggregation: the values of a numeric field below which the given percentages of the
 * documents fall.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/percentile/
 */
class Percentiles implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The numeric field
     * @param array<int, int|float>|null $percents The percentiles to calculate, e.g. [50, 95, 99]. Null leaves it out
     *                                             so OpenSearch's default ([1, 5, 25, 50, 75, 95, 99]) applies
     */
    public function __construct(
        private readonly string $field,
        private readonly ?array $percents = null,
    ){}

    /**
     * @param string $field The numeric field
     * @param array<int, int|float>|null $percents The percentiles to calculate, e.g. [50, 95, 99]. Null leaves it out
     *                                             so OpenSearch's default ([1, 5, 25, 50, 75, 95, 99]) applies
     * @return self
     */
    public static function make(string $field, ?array $percents = null): self
    {
        return new self($field, $percents);
    }

    /**
     * @return array{percentiles: array{field: string, percents?: array<int, int|float>}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'percentiles' => [
                'field' => $this->field,
            ]
        ];

        if (!is_null($this->percents)) {
            $query['percentiles']['percents'] = $this->percents;
        }

        return $query;
    }
}
