<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `multi_terms` bucket aggregation: one bucket per combination of the values of several fields, ordered by
 * document count.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/multi-terms/
 */
class MultiTerms implements OpenSearchQuery, AggregationType
{
    /**
     * @param array<array-key, string> $fields The fields to combine, in order; the keys are dropped
     * @param int $size The number of buckets to return. Always sent; 10 is also OpenSearch's default
     */
    public function __construct(
        private readonly array $fields,
        private readonly int $size
    ){}

    /**
     * @param array<array-key, string> $fields The fields to combine, in order; the keys are dropped
     * @param int $size The number of buckets to return. Always sent; 10 is also OpenSearch's default
     * @return self
     */
    public static function make(array $fields, int $size = 10): self
    {
        return new self($fields, $size);
    }

    /**
     * @return array{multi_terms: array{terms: list<array{field: string}>, size: int}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'multi_terms' => [
                'terms' => array_map(fn(string $field) => ['field' => $field], array_values($this->fields)),
                'size' => $this->size,
            ]
        ];
    }
}
