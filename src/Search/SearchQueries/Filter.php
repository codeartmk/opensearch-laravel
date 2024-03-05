<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

class Filter implements OpenSearchQuery
{
    public function __construct(
        private SearchQueryType|array $queryType
    ){}

    public static function make(SearchQueryType|array $queryType): self
    {
        return new self($queryType);
    }

    public function toOpenSearchQuery(): array
    {
        if(!is_array($this->queryType)) {
            return [
                ...$this->queryType->toOpenSearchQuery()
            ];
        }

        $resulting = [];

        foreach ($this->queryType as $parameter) {
            $resulting[] = [...$parameter->toOpenSearchQuery()];
        }

        return $resulting;
    }
}
