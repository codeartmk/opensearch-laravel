<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

class Boosting implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly SearchQueryType|BoolQuery $positive,
        private readonly SearchQueryType|BoolQuery $negative,
        private readonly int|float $negativeBoost
    ){}

    public static function make(
        SearchQueryType|BoolQuery $positive,
        SearchQueryType|BoolQuery $negative,
        int|float $negativeBoost
    ): self
    {
        return new self($positive, $negative, $negativeBoost);
    }

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
