<?php

namespace Codeart\OpensearchLaravel\Search;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * Merges the Sort and the Query set by OpenSearchBuilder::search() into one request body. Both are optional.
 */
class SearchBuilder implements OpenSearchQuery
{
    private Sort $sort;
    private Query $query;

    /**
     * Sets the sort, replacing an earlier one.
     *
     * @param Sort $sort
     * @return SearchBuilder
     */
    public function setSort(Sort $sort): SearchBuilder
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * Sets the query, replacing an earlier one.
     *
     * @param Query $query
     * @return SearchBuilder
     */
    public function setQuery(Query $query): SearchBuilder
    {
        $this->query = $query;
        return $this;
    }


    /**
     * @return array{sort?: array<array-key, mixed>, query?: array<string, mixed>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            ...(isset($this->sort) ? $this->sort->toOpenSearchQuery() : []),
            ...(isset($this->query) ? $this->query->toOpenSearchQuery() : [])
        ];
    }
}
