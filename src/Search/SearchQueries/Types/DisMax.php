<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

/**
 * A `dis_max` query: matches documents that match any of the queries, scored by the best-matching one.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/compound/disjunction-max/
 */
class DisMax implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param array<array-key, SearchQueryType|BoolQuery> $queries The queries; the keys are dropped. The items are not
     *                                                             checked, so anything else fails when the request is
     *                                                             built
     * @param int|float|null $tieBreaker How much, between 0 and 1, the other matching queries add to the score. Null
     *                                   leaves it out so OpenSearch's default (0) applies
     */
    public function __construct(
        private readonly array $queries,
        private readonly int|float|null $tieBreaker
    ){}

    /**
     * @param array<array-key, SearchQueryType|BoolQuery> $queries The queries; the keys are dropped. The items are not
     *                                                             checked, so anything else fails when the request is
     *                                                             built
     * @param int|float|null $tieBreaker How much, between 0 and 1, the other matching queries add to the score. Null
     *                                   leaves it out so OpenSearch's default (0) applies
     * @return self
     */
    public static function make(array $queries, int|float|null $tieBreaker = null): self
    {
        return new self($queries, $tieBreaker);
    }

    /**
     * @return array{dis_max: array{queries: list<array<string, mixed>>, tie_breaker?: int|float}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'dis_max' => [
                'queries' => array_map(fn($query) => $query->toOpenSearchQuery(), array_values($this->queries))
            ]
        ];

        if (!is_null($this->tieBreaker)) {
            $query['dis_max']['tie_breaker'] = $this->tieBreaker;
        }

        return $query;
    }
}
