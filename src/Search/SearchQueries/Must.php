<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

class Must extends BoolClause
{
    /**
     * @param SearchQueryType|BoolQuery|array<SearchQueryType|BoolQuery> $queryType
     * @throws InvalidSearchParametersException
     */
    public static function make(SearchQueryType|BoolQuery|array $queryType): self
    {
        return new self($queryType);
    }
}
