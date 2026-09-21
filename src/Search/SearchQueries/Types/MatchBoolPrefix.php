<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `match_bool_prefix` query: analyzes the text into a bool query of term queries, with the last term used as a prefix.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/full-text/match-bool-prefix/
 */
class MatchBoolPrefix implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string $value The text to analyze
     */
    public function __construct(
        private readonly string $field,
        private readonly string $value
    ){}

    /**
     * @param string $field The field to search
     * @param string $value The text to analyze
     * @return self
     */
    public static function make(string $field, string $value): self
    {
        return new self($field, $value);
    }

    /**
     * @return array{match_bool_prefix: array<string, string>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'match_bool_prefix' => [
                $this->field => $this->value
            ]
        ];
    }
}
