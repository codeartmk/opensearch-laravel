<?php

namespace Codeart\OpensearchLaravel\Search;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class SearchBuilder implements OpenSearchQuery
{
    private Sort $sort;
    private Query $query;

    /**
     * @param Sort $sort
     * @return SearchBuilder
     */
    public function setSort(Sort $sort): SearchBuilder
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param Query $query
     * @return SearchBuilder
     */
    public function setQuery(Query $query): SearchBuilder
    {
        $this->query = $query;
        return $this;
    }


    public function toOpenSearchQuery(): array
    {
        return [
            ...(isset($this->sort) ? $this->sort->toOpenSearchQuery() : []),
            ...(isset($this->query) ? $this->query->toOpenSearchQuery() : [])
        ];
    }
}
