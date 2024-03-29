<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

class MustNot implements OpenSearchQuery
{
    public function __construct(
        private readonly SearchQueryType|BoolQuery|array $queryType
    ){}

    public static function make(SearchQueryType|BoolQuery|array $queryType): self
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
