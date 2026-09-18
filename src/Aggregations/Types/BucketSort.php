<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `bucket_sort` pipeline aggregation: sorts the buckets of the parent multi-bucket aggregation, and with $size
 * and $from keeps only a page of them. Without a $field it only truncates, keeping the buckets in the parent's order.
 *
 * @see https://opensearch.org/docs/latest/aggregations/pipeline/bucket-sort/
 */
class BucketSort implements OpenSearchQuery, AggregationType
{
    /**
     * @param string|null $field What to sort the buckets by: `_key`, `_count` or the path to a metric, e.g.
     *                           'total_sales'. Null leaves `sort` out, so the buckets keep the parent's order
     * @param string|null $order asc or desc. Null sends the bare field name so OpenSearch's default (asc) applies.
     *                           Requires a $field
     * @param int|null $size The number of buckets to keep, at least 1. Null leaves it out so every bucket is kept
     * @param int|null $from The number of buckets to skip, 0 or more. Null leaves it out so OpenSearch's default (0)
     *                       applies
     * @throws InvalidAggregationParametersException When $order is given without a $field, or when there is no
     *                                               $field, no $size and no $from other than 0, so the
     *                                               aggregation would do nothing, or when $size is below 1 or
     *                                               $from is below 0
     */
    public function __construct(
        private readonly ?string $field = null,
        private readonly ?string $order = null,
        private readonly ?int $size = null,
        private readonly ?int $from = null,
    ){
        if (is_null($field) && !is_null($order)) {
            throw new InvalidAggregationParametersException('BucketSort requires a field to apply an order to.');
        }

        // OpenSearch rejects these while parsing: "[size] must be a positive integer" and
        // "[from] must be a non-negative integer".
        if (!is_null($size) && $size < 1) {
            throw new InvalidAggregationParametersException("BucketSort size must be at least 1, {$size} given.");
        }

        if (!is_null($from) && $from < 0) {
            throw new InvalidAggregationParametersException("BucketSort from must be 0 or more, {$from} given.");
        }

        // OpenSearch rejects a bucket_sort without sort, size or from, and also one with only "from": 0:
        // "[name] is configured to perform nothing. Please set either of [sort, size, from] to use bucket_sort".
        if (is_null($field) && is_null($size) && !$from) {
            throw new InvalidAggregationParametersException(
                'BucketSort requires a field to sort by, a size or a from above 0.'
            );
        }
    }

    /**
     * @param string|null $field What to sort the buckets by: `_key`, `_count` or the path to a metric, e.g.
     *                           'total_sales'. Null leaves `sort` out, so the buckets keep the parent's order
     * @param string|null $order asc or desc. Null sends the bare field name so OpenSearch's default (asc) applies.
     *                           Requires a $field
     * @param int|null $size The number of buckets to keep, at least 1. Null leaves it out so every bucket is kept
     * @param int|null $from The number of buckets to skip, 0 or more. Null leaves it out so OpenSearch's default (0)
     *                       applies
     * @return self
     * @throws InvalidAggregationParametersException When $order is given without a $field, or when there is no
     *                                               $field, no $size and no $from other than 0, so the
     *                                               aggregation would do nothing, or when $size is below 1 or
     *                                               $from is below 0
     */
    public static function make(
        ?string $field = null,
        ?string $order = null,
        ?int $size = null,
        ?int $from = null
    ): self
    {
        return new self($field, $order, $size, $from);
    }

    /**
     * @return array{bucket_sort: array{sort?: list<string|array<string, array{order: string}>>, size?: int, from?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = ['bucket_sort' => []];

        if (!is_null($this->field)) {
            $query['bucket_sort']['sort'] = [
                is_null($this->order) ? $this->field : [$this->field => ['order' => $this->order]],
            ];
        }

        if (!is_null($this->size)) {
            $query['bucket_sort']['size'] = $this->size;
        }

        if (!is_null($this->from)) {
            $query['bucket_sort']['from'] = $this->from;
        }

        return $query;
    }
}
