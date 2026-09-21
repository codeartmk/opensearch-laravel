<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `match_phrase_prefix` query: matches the phrase with its last term used as a prefix, e.g. for search-as-you-type.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/full-text/match-phrase-prefix/
 */
class MatchPhrasePrefix implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string|bool|int|array<string, mixed> $value The phrase, or the options object, e.g. ['query' => 'the wind
     *                                                    ri', 'max_expansions' => 10]
     */
    public function __construct(
        private readonly string $field,
        private readonly string|bool|int|array $value
    ){}

    /**
     * @param string $field The field to search
     * @param string|bool|int|array<string, mixed> $value The phrase, or the options object, e.g. ['query' => 'the wind
     *                                                    ri', 'max_expansions' => 10]
     * @return self
     */
    public static function make(string $field, string|bool|int|array $value): self
    {
        return new self($field, $value);
    }

    /**
     * @return array{match_phrase_prefix: array<string, string|bool|int|array<string, mixed>>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'match_phrase_prefix' => [
                $this->field => $this->value
            ]
        ];
    }
}
