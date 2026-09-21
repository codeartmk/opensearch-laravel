<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `bucket_script` pipeline aggregation: a value calculated by a script from metrics of each bucket of the parent
 * multi-bucket aggregation.
 *
 * @see https://opensearch.org/docs/latest/aggregations/pipeline/bucket-script/
 */
class BucketScript implements OpenSearchQuery, AggregationType
{
    /**
     * @param array<string, string> $bucketsPath Script variable name => buckets path, e.g. ['sales' => 'total_sales',
     *                                           'count' => '_count']; the script reads them as params.sales and
     *                                           params.count
     * @param string $script The script that calculates the value, e.g. 'params.sales / params.count'
     * @param string|null $gapPolicy How gaps (buckets with no value) are handled: skip, insert_zeros or keep_values.
     *                               Null leaves it out so OpenSearch's default (skip) applies
     */
    public function __construct(
        private readonly array $bucketsPath,
        private readonly string $script,
        private readonly ?string $gapPolicy
    ){}

    /**
     * @param array<string, string> $bucketsPath Script variable name => buckets path, e.g. ['sales' => 'total_sales',
     *                                           'count' => '_count']; the script reads them as params.sales and
     *                                           params.count
     * @param string $script The script that calculates the value, e.g. 'params.sales / params.count'
     * @param string|null $gapPolicy How gaps (buckets with no value) are handled: skip, insert_zeros or keep_values.
     *                               Null leaves it out so OpenSearch's default (skip) applies
     * @return self
     */
    public static function make(array $bucketsPath, string $script, ?string $gapPolicy = null): self
    {
        return new self($bucketsPath, $script, $gapPolicy);
    }

    /**
     * @return array{bucket_script: array{buckets_path: array<string, string>, script: string, gap_policy?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'bucket_script' => [
                'buckets_path' => $this->bucketsPath,
                'script' => $this->script,
            ]
        ];

        if (!is_null($this->gapPolicy)) {
            $query['bucket_script']['gap_policy'] = $this->gapPolicy;
        }

        return $query;
    }
}
