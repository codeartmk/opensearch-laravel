<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `histogram` bucket aggregation: buckets documents by a numeric field into fixed-width intervals.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/histogram/
 */
class Histogram implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The numeric field
     * @param int|float $interval The width of each bucket
     * @param int|null $minDocCount Buckets with fewer documents are left out. Null leaves it out so OpenSearch's
     *                              default (0, every bucket in the range is returned) applies
     */
    public function __construct(
        private readonly string $field,
        private readonly int|float $interval,
        private readonly ?int $minDocCount
    ){}

    /**
     * @param string $field The numeric field
     * @param int|float $interval The width of each bucket
     * @param int|null $minDocCount Buckets with fewer documents are left out. Null leaves it out so OpenSearch's
     *                              default (0, every bucket in the range is returned) applies
     * @return self
     */
    public static function make(string $field, int|float $interval, ?int $minDocCount = null): self
    {
        return new self($field, $interval, $minDocCount);
    }

    /**
     * @return array{histogram: array{field: string, interval: int|float, min_doc_count?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'histogram' => [
                'field' => $this->field,
                'interval' => $this->interval,
            ]
        ];

        if (!is_null($this->minDocCount)) {
            $query['histogram']['min_doc_count'] = $this->minDocCount;
        }

        return $query;
    }
}
