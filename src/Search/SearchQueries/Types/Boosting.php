<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

/**
 * A `boosting` query: returns the documents that match `positive` and lowers the score of those that also match `negative`.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/compound/boosting/
 */
class Boosting implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param SearchQueryType|BoolQuery $positive The query documents must match
     * @param SearchQueryType|BoolQuery $negative The query whose matches are demoted, not excluded
     * @param int|float $negativeBoost The factor, between 0 and 1, a negative match's score is multiplied by
     */
    public function __construct(
        private readonly SearchQueryType|BoolQuery $positive,
        private readonly SearchQueryType|BoolQuery $negative,
        private readonly int|float $negativeBoost
    ){}

    /**
     * @param SearchQueryType|BoolQuery $positive The query documents must match
     * @param SearchQueryType|BoolQuery $negative The query whose matches are demoted, not excluded
     * @param int|float $negativeBoost The factor, between 0 and 1, a negative match's score is multiplied by
     * @return self
     */
    public static function make(
        SearchQueryType|BoolQuery $positive,
        SearchQueryType|BoolQuery $negative,
        int|float $negativeBoost
    ): self
    {
        return new self($positive, $negative, $negativeBoost);
    }

    /**
     * @return array{boosting: array{positive: array<string, mixed>, negative: array<string, mixed>, negative_boost: int|float}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'boosting' => [
                'positive' => $this->positive->toOpenSearchQuery(),
                'negative' => $this->negative->toOpenSearchQuery(),
                'negative_boost' => $this->negativeBoost
            ]
        ];
    }
}
