<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `bucket_selector` pipeline aggregation: keeps only the buckets of the parent multi-bucket aggregation for which
 * a script returns true.
 *
 * @see https://opensearch.org/docs/latest/aggregations/pipeline/bucket-selector/
 */
class BucketSelector implements OpenSearchQuery, AggregationType
{
    /**
     * @param array<string, string> $bucketsPath Script variable name => buckets path, e.g. ['sales' => 'total_sales'];
     *                                           the script reads them as params.sales
     * @param string $script The condition a bucket must meet to be kept, e.g. 'params.sales > 1000'
     * @param string|null $gapPolicy How gaps (buckets with no value) are handled: skip, insert_zeros or keep_values.
     *                               Null leaves it out so OpenSearch's default (skip) applies
     */
    public function __construct(
        private readonly array $bucketsPath,
        private readonly string $script,
        private readonly ?string $gapPolicy
    ){}

    /**
     * @param array<string, string> $bucketsPath Script variable name => buckets path, e.g. ['sales' => 'total_sales'];
     *                                           the script reads them as params.sales
     * @param string $script The condition a bucket must meet to be kept, e.g. 'params.sales > 1000'
     * @param string|null $gapPolicy How gaps (buckets with no value) are handled: skip, insert_zeros or keep_values.
     *                               Null leaves it out so OpenSearch's default (skip) applies
     * @return self
     */
    public static function make(array $bucketsPath, string $script, ?string $gapPolicy = null): self
    {
        return new self($bucketsPath, $script, $gapPolicy);
    }

    /**
     * @return array{bucket_selector: array{buckets_path: array<string, string>, script: string, gap_policy?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'bucket_selector' => [
                'buckets_path' => $this->bucketsPath,
                'script' => $this->script,
            ]
        ];

        if (!is_null($this->gapPolicy)) {
            $query['bucket_selector']['gap_policy'] = $this->gapPolicy;
        }

        return $query;
    }
}
