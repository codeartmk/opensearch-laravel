<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `bucket_sort` pipeline aggregation: sorts the buckets of the parent multi-bucket aggregation, and with $size
 * and $from keeps only a page of them.
 *
 * @see https://opensearch.org/docs/latest/aggregations/pipeline/bucket-sort/
 */
class BucketSort implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field What to sort the buckets by: `_key`, `_count` or the path to a metric, e.g. 'total_sales'
     * @param string|null $order asc or desc. Null sends the bare field name so OpenSearch's default (asc) applies
     * @param int|null $size The number of buckets to keep. Null leaves it out so every bucket is kept
     * @param int|null $from The number of buckets to skip. Null leaves it out so OpenSearch's default (0) applies
     */
    public function __construct(
        private readonly string $field,
        private readonly ?string $order = null,
        private readonly ?int $size = null,
        private readonly ?int $from = null,
    ){}

    /**
     * @param string $field What to sort the buckets by: `_key`, `_count` or the path to a metric, e.g. 'total_sales'
     * @param string|null $order asc or desc. Null sends the bare field name so OpenSearch's default (asc) applies
     * @param int|null $size The number of buckets to keep. Null leaves it out so every bucket is kept
     * @param int|null $from The number of buckets to skip. Null leaves it out so OpenSearch's default (0) applies
     * @return self
     */
    public static function make(string $field, ?string $order = null, ?int $size = null, ?int $from = null): self
    {
        return new self($field, $order, $size, $from);
    }

    /**
     * @return array{bucket_sort: array{sort: list<string|array<string, array{order: string}>>, size?: int, from?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'bucket_sort' => [
                'sort' => [
                    is_null($this->order) ? $this->field : [$this->field => ['order' => $this->order]],
                ],
            ]
        ];

        if (!is_null($this->size)) {
            $query['bucket_sort']['size'] = $this->size;
        }

        if (!is_null($this->from)) {
            $query['bucket_sort']['from'] = $this->from;
        }

        return $query;
    }
}
