<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * An `extended_stats` metric aggregation: Stats plus the sum of squares, variance, standard deviation and its
 * bounds for a numeric field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/extended-stats/
 */
class ExtendedStats implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The numeric field
     * @param int|float|null $sigma How many standard deviations above and below the mean the std_deviation_bounds are
     *                              placed. Null leaves it out so OpenSearch's default (2) applies
     */
    public function __construct(
        private readonly string $field,
        private readonly int|float|null $sigma
    ){}

    /**
     * @param string $field The numeric field
     * @param int|float|null $sigma How many standard deviations above and below the mean the std_deviation_bounds are
     *                              placed. Null leaves it out so OpenSearch's default (2) applies
     * @return self
     */
    public static function make(string $field, int|float|null $sigma = null): self
    {
        return new self($field, $sigma);
    }

    /**
     * @return array{extended_stats: array{field: string, sigma?: int|float}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'extended_stats' => [
                'field' => $this->field,
            ]
        ];

        if (!is_null($this->sigma)) {
            $query['extended_stats']['sigma'] = $this->sigma;
        }

        return $query;
    }
}
