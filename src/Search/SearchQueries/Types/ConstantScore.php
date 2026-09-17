<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

class ConstantScore implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly SearchQueryType|BoolQuery $filter,
        private readonly int|float|null $boost
    ){}

    public static function make(SearchQueryType|BoolQuery $filter, int|float|null $boost = null): self
    {
        return new self($filter, $boost);
    }

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
