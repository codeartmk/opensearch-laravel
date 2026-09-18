<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

/**
 * A `function_score` query: recalculates the score of the matching documents with one or more functions.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/compound/function-score/
 */
class FunctionScore implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param array<array-key, array<string, mixed>> $functions The function definitions, e.g. ['field_value_factor' =>
     *                                                          [...], 'weight' => 2]. A `filter` may be given as a
     *                                                          query object instead of an array; the keys of the list
     *                                                          are dropped
     * @param SearchQueryType|BoolQuery|null $query The query whose matches are rescored. Null leaves it out so
     *                                              OpenSearch's default (match_all) applies
     * @param string|null $scoreMode How the function scores are combined: multiply, sum, avg, first, max or min. Null
     *                               leaves it out so OpenSearch's default (multiply) applies
     * @param string|null $boostMode How the combined function score is combined with the query score: multiply,
     *                               replace, sum, avg, max or min. Null leaves it out so OpenSearch's default
     *                               (multiply) applies
     * @param int|float|null $maxBoost The upper limit of the function score. Null leaves it out so OpenSearch's default
     *                                 (no limit) applies
     * @param int|float|null $minScore Documents scoring below this are dropped. Null leaves it out so OpenSearch's
     *                                 default (none dropped) applies
     */
    public function __construct(
        private readonly array $functions,
        private readonly SearchQueryType|BoolQuery|null $query,
        private readonly ?string $scoreMode,
        private readonly ?string $boostMode,
        private readonly int|float|null $maxBoost,
        private readonly int|float|null $minScore
    ){}

    /**
     * @param array<array-key, array<string, mixed>> $functions The function definitions, e.g. ['field_value_factor' =>
     *                                                          [...], 'weight' => 2]. A `filter` may be given as a
     *                                                          query object instead of an array; the keys of the list
     *                                                          are dropped
     * @param SearchQueryType|BoolQuery|null $query The query whose matches are rescored. Null leaves it out so
     *                                              OpenSearch's default (match_all) applies
     * @param string|null $scoreMode How the function scores are combined: multiply, sum, avg, first, max or min. Null
     *                               leaves it out so OpenSearch's default (multiply) applies
     * @param string|null $boostMode How the combined function score is combined with the query score: multiply,
     *                               replace, sum, avg, max or min. Null leaves it out so OpenSearch's default
     *                               (multiply) applies
     * @param int|float|null $maxBoost The upper limit of the function score. Null leaves it out so OpenSearch's default
     *                                 (no limit) applies
     * @param int|float|null $minScore Documents scoring below this are dropped. Null leaves it out so OpenSearch's
     *                                 default (none dropped) applies
     * @return self
     */
    public static function make(
        array $functions,
        SearchQueryType|BoolQuery|null $query = null,
        ?string $scoreMode = null,
        ?string $boostMode = null,
        int|float|null $maxBoost = null,
        int|float|null $minScore = null
    ): self
    {
        return new self($functions, $query, $scoreMode, $boostMode, $maxBoost, $minScore);
    }

    /**
     * @return array{function_score: array{functions: list<array<string, mixed>>, query?: array<string, mixed>, score_mode?: string, boost_mode?: string, max_boost?: int|float, min_score?: int|float}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'function_score' => [
                'functions' => array_map(function (array $function) {
                    if (isset($function['filter']) && $function['filter'] instanceof OpenSearchQuery) {
                        $function['filter'] = $function['filter']->toOpenSearchQuery();
                    }

                    return $function;
                }, array_values($this->functions))
            ]
        ];

        if (!is_null($this->query)) {
            $query['function_score']['query'] = $this->query->toOpenSearchQuery();
        }

        if (!is_null($this->scoreMode)) {
            $query['function_score']['score_mode'] = $this->scoreMode;
        }

        if (!is_null($this->boostMode)) {
            $query['function_score']['boost_mode'] = $this->boostMode;
        }

        if (!is_null($this->maxBoost)) {
            $query['function_score']['max_boost'] = $this->maxBoost;
        }

        if (!is_null($this->minScore)) {
            $query['function_score']['min_score'] = $this->minScore;
        }

        return $query;
    }
}
