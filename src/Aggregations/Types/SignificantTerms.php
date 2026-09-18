<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `significant_terms` bucket aggregation: the terms that are unusually frequent in the matching documents
 * compared with the whole index.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/significant-terms/
 */
class SignificantTerms implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The field whose terms are compared, usually a keyword field
     * @param int|null $size The number of terms to return. Null leaves it out so OpenSearch's default (10) applies
     */
    public function __construct(
        private readonly string $field,
        private readonly ?int $size
    ){}

    /**
     * @param string $field The field whose terms are compared, usually a keyword field
     * @param int|null $size The number of terms to return. Null leaves it out so OpenSearch's default (10) applies
     * @return self
     */
    public static function make(string $field, ?int $size = null): self
    {
        return new self($field, $size);
    }

    /**
     * @return array{significant_terms: array{field: string, size?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'significant_terms' => [
                'field' => $this->field,
            ]
        ];

        if (!is_null($this->size)) {
            $query['significant_terms']['size'] = $this->size;
        }

        return $query;
    }
}
