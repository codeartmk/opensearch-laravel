<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

class Filter implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly SearchQueryType|BoolQuery $query
    ){}

    public static function make(SearchQueryType|BoolQuery $query): self
    {
        return new self($query);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'filter' => $this->query->toOpenSearchQuery()
        ];
    }
}
