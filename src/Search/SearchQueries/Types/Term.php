<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `term` query: matches the exact value, which is not analyzed. Meant for keyword, numeric, boolean and date
 * fields; on a text field, use MatchOne.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/term/
 */
class Term implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string|bool|int $value The exact value to match
     */
    public function __construct(
        private readonly string $field,
        private readonly string|bool|int $value
    ){}

    /**
     * @param string $field The field to search
     * @param string|bool|int $value The exact value to match
     * @return self
     */
    public static function make(string $field, string|bool|int $value): self
    {
        return new self($field, $value);
    }

    /**
     * @return array{term: array<string, string|bool|int>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'term' => [
                $this->field => $this->value
            ]
        ];
    }
}
