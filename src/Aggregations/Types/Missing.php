<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `missing` bucket aggregation: a single bucket holding the documents that have no value for a field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/missing/
 */
class Missing implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The field that must be missing
     */
    public function __construct(
        private readonly string $field,
    ){}

    /**
     * @param string $field The field that must be missing
     * @return self
     */
    public static function make(string $field): self
    {
        return new self($field);
    }

    /**
     * @return array{missing: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'missing' => [
                'field' => $this->field,
            ]
        ];
    }
}
