<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `derivative` pipeline aggregation: the change of a metric from one bucket to the next of a parent histogram or
 * date histogram.
 *
 * @see https://opensearch.org/docs/latest/aggregations/pipeline/derivative/
 */
class Derivative implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $bucketsPath The path to the metric, relative to the parent aggregation, e.g. 'sales'
     * @param string|null $gapPolicy How gaps (buckets with no value) are handled: skip, insert_zeros or keep_values.
     *                               Null leaves it out so OpenSearch's default (skip) applies
     */
    public function __construct(
        private readonly string $bucketsPath,
        private readonly ?string $gapPolicy
    ){}

    /**
     * @param string $bucketsPath The path to the metric, relative to the parent aggregation, e.g. 'sales'
     * @param string|null $gapPolicy How gaps (buckets with no value) are handled: skip, insert_zeros or keep_values.
     *                               Null leaves it out so OpenSearch's default (skip) applies
     * @return self
     */
    public static function make(string $bucketsPath, ?string $gapPolicy = null): self
    {
        return new self($bucketsPath, $gapPolicy);
    }

    /**
     * @return array{derivative: array{buckets_path: string, gap_policy?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'derivative' => [
                'buckets_path' => $this->bucketsPath,
            ]
        ];

        if (!is_null($this->gapPolicy)) {
            $query['derivative']['gap_policy'] = $this->gapPolicy;
        }

        return $query;
    }
}
