<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
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
     * @param non-empty-array<array-key, SearchQueryType|BoolQuery> $queries The queries; the keys are dropped
     * @param int|float|null $tieBreaker How much, between 0 and 1, the other matching queries add to the score. Null
     *                                   leaves it out so OpenSearch's default (0) applies
     * @throws InvalidSearchParametersException When the list is empty or has an item that is neither a SearchQueryType
     *                                          nor a BoolQuery
     */
    public function __construct(
        private readonly array $queries,
        private readonly int|float|null $tieBreaker
    ){
        // OpenSearch rejects "queries": [] ("query malformed, must start with start_object").
        if (!count($queries)) {
            throw new InvalidSearchParametersException('DisMax requires at least one query.');
        }

        foreach ($queries as $key => $query) {
            if (!$query instanceof SearchQueryType && !$query instanceof BoolQuery) {
                throw new InvalidSearchParametersException(sprintf(
                    'DisMax accepts only SearchQueryType or BoolQuery queries, %s given at key %s.',
                    get_debug_type($query),
                    var_export($key, true)
                ));
            }
        }
    }

    /**
     * @param non-empty-array<array-key, SearchQueryType|BoolQuery> $queries The queries; the keys are dropped
     * @param int|float|null $tieBreaker How much, between 0 and 1, the other matching queries add to the score. Null
     *                                   leaves it out so OpenSearch's default (0) applies
     * @return self
     * @throws InvalidSearchParametersException When the list is empty or has an item that is neither a SearchQueryType
     *                                          nor a BoolQuery
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
