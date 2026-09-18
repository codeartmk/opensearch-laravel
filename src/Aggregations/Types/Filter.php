<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

/**
 * A `filter` bucket aggregation: a single bucket holding the documents that match a query, usually with
 * sub-aggregations. Not to be confused with the Filter clause of a BoolQuery.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/filter/
 */
class Filter implements OpenSearchQuery, AggregationType
{
    /**
     * @param SearchQueryType|BoolQuery $query The query the documents in the bucket must match
     */
    public function __construct(
        private readonly SearchQueryType|BoolQuery $query
    ){}

    /**
     * @param SearchQueryType|BoolQuery $query The query the documents in the bucket must match
     * @return self
     */
    public static function make(SearchQueryType|BoolQuery $query): self
    {
        return new self($query);
    }

    /**
     * @return array{filter: array<string, mixed>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'filter' => $this->query->toOpenSearchQuery()
        ];
    }
}
