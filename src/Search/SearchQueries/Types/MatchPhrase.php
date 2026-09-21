<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `match_phrase` query: matches the analyzed terms as a phrase, in order.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/full-text/match-phrase/
 */
class MatchPhrase implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string $value The phrase
     * @param int|null $slop How many positions apart the terms may be. Null sends the short form without it, so
     *                       OpenSearch's default (0) applies
     */
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly ?int $slop
    ){}

    /**
     * @param string $field The field to search
     * @param string $value The phrase
     * @param int|null $slop How many positions apart the terms may be. Null sends the short form without it, so
     *                       OpenSearch's default (0) applies
     * @return self
     */
    public static function make(string $field, string $value, ?int $slop = null): self
    {
        return new self($field, $value, $slop);
    }

    /**
     * @return array{match_phrase: array<string, string|array{query: string, slop: int}>}
     */
    public function toOpenSearchQuery(): array
    {
        if (is_null($this->slop)) {
            return [
                'match_phrase' => [
                    $this->field => $this->value
                ]
            ];
        }

        return [
            'match_phrase' => [
                $this->field => [
                    'query' => $this->value,
                    'slop' => $this->slop
                ]
            ]
        ];
    }
}
