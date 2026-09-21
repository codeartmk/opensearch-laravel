<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `max_bucket` pipeline aggregation: the largest value of a metric across the buckets of a sibling aggregation,
 * and the keys of the buckets that hold it.
 *
 * @see https://opensearch.org/docs/latest/aggregations/pipeline/max-bucket/
 */
class MaxBucket implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $bucketsPath The path to the metric to process, e.g. 'sales_per_month>sales'
     * @param string|null $gapPolicy How gaps (buckets with no value) are handled: skip, insert_zeros or keep_values.
     *                               Null leaves it out so OpenSearch's default (skip) applies
     */
    public function __construct(
        private readonly string $bucketsPath,
        private readonly ?string $gapPolicy
    ){}

    /**
     * @param string $bucketsPath The path to the metric to process, e.g. 'sales_per_month>sales'
     * @param string|null $gapPolicy How gaps (buckets with no value) are handled: skip, insert_zeros or keep_values.
     *                               Null leaves it out so OpenSearch's default (skip) applies
     * @return self
     */
    public static function make(string $bucketsPath, ?string $gapPolicy = null): self
    {
        return new self($bucketsPath, $gapPolicy);
    }

    /**
     * @return array{max_bucket: array{buckets_path: string, gap_policy?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'max_bucket' => [
                'buckets_path' => $this->bucketsPath,
            ]
        ];

        if (!is_null($this->gapPolicy)) {
            $query['max_bucket']['gap_policy'] = $this->gapPolicy;
        }

        return $query;
    }
}
