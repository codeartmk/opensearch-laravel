<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * An `exists` query: matches documents that have an indexed value for the field.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/exists/
 */
class Exists implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field that must have a value
     */
    public function __construct(
        private readonly string $field,
    ){}

    /**
     * @param string $field The field that must have a value
     * @return self
     */
    public static function make(string $field): self
    {
        return new self($field);
    }

    /**
     * @return array{exists: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'exists' => [
                'field' => $this->field
            ]
        ];
    }
}