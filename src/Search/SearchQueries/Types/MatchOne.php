<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `match` query, the standard full-text query: the value is analyzed and matched against the field.
 * Named MatchOne because `match` is a PHP reserved word.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/full-text/match/
 */
class MatchOne implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string|bool|int|array<string, mixed> $value The text to search for, or the options object, e.g. ['query'
     *                                                    => 'wind', 'operator' => 'and']
     */
    public function __construct(
        private readonly string $field,
        private readonly string|bool|int|array $value
    ){}

    /**
     * @param string $field The field to search
     * @param string|bool|int|array<string, mixed> $value The text to search for, or the options object, e.g. ['query'
     *                                                    => 'wind', 'operator' => 'and']
     * @return self
     */
    public static function make(string $field, string|bool|int|array $value): self
    {
        return new self($field, $value);
    }

    /**
     * @return array{match: array<string, string|bool|int|array<string, mixed>>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'match' => [
                $this->field => $this->value
            ]
        ];
    }
}
