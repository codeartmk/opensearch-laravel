<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `terms` bucket aggregation: one bucket per unique value of a field. Not to be confused with the terms query.
 *
 * It always sends `size`, so it can't be used as a Composite source; use Composite's string shorthand there
 * instead.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/terms/
 */
class Terms implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The field to bucket by, usually a keyword field
     * @param int $size The number of buckets to return. Always sent; 10 is also OpenSearch's default
     * @param array<array-key, mixed>|null $order The bucket order, e.g. ['_count' => 'asc'] or ['_key' => 'desc']. Null
     *                                            leaves it out so OpenSearch's default (by document count, descending)
     *                                            applies
     * @param int|null $minDocCount Buckets with fewer documents are left out. Null leaves it out so OpenSearch's
     *                              default (1) applies
     */
    public function __construct(
        private readonly string $field,
        private readonly int $size,
        private readonly ?array $order = null,
        private readonly ?int $minDocCount = null,
    ){}

    /**
     * @param string $field The field to bucket by, usually a keyword field
     * @param int $size The number of buckets to return. Always sent; 10 is also OpenSearch's default
     * @param array<array-key, mixed>|null $order The bucket order, e.g. ['_count' => 'asc'] or ['_key' => 'desc']. Null
     *                                            leaves it out so OpenSearch's default (by document count, descending)
     *                                            applies
     * @param int|null $minDocCount Buckets with fewer documents are left out. Null leaves it out so OpenSearch's
     *                              default (1) applies
     * @return self
     */
    public static function make(string $field, int $size = 10, ?array $order = null, ?int $minDocCount = null): self
    {
        return new self($field, $size, $order, $minDocCount);
    }

    /**
     * @return array{terms: array{field: string, size: int, order?: array<array-key, mixed>, min_doc_count?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'terms' => [
                'field' => $this->field,
                'size' => $this->size
            ]
        ];

        if (!is_null($this->order)) {
            $query['terms']['order'] = $this->order;
        }

        if (!is_null($this->minDocCount)) {
            $query['terms']['min_doc_count'] = $this->minDocCount;
        }

        return $query;
    }
}
