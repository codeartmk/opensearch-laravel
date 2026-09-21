<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

/**
 * A `constant_score` query: runs the query in filter context and gives every match the same score.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/compound/constant-score/
 */
class ConstantScore implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param SearchQueryType|BoolQuery $filter The query documents must match
     * @param int|float|null $boost The score of every match. Null leaves it out so OpenSearch's default (1.0) applies
     */
    public function __construct(
        private readonly SearchQueryType|BoolQuery $filter,
        private readonly int|float|null $boost
    ){}

    /**
     * @param SearchQueryType|BoolQuery $filter The query documents must match
     * @param int|float|null $boost The score of every match. Null leaves it out so OpenSearch's default (1.0) applies
     * @return self
     */
    public static function make(SearchQueryType|BoolQuery $filter, int|float|null $boost = null): self
    {
        return new self($filter, $boost);
    }

    /**
     * @return array{constant_score: array{filter: array<string, mixed>, boost?: int|float}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'constant_score' => [
                'filter' => $this->filter->toOpenSearchQuery()
            ]
        ];

        if (!is_null($this->boost)) {
            $query['constant_score']['boost'] = $this->boost;
        }

        return $query;
    }
}
