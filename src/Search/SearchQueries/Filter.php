<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

/**
 * The `filter` clause of a BoolQuery. All of the queries must match, in filter context: they don't affect the score and can be cached.
 *
 * Takes one query or a list of them, see BoolClause.
 */
class Filter extends BoolClause
{
    /**
     * @param SearchQueryType|BoolQuery|array<array-key, SearchQueryType|BoolQuery> $queryType One query, or a list of them
     * @return self
     * @throws InvalidSearchParametersException When a list item is neither a SearchQueryType nor a BoolQuery
     */
    public static function make(SearchQueryType|BoolQuery|array $queryType): self
    {
        return new self($queryType);
    }
}
