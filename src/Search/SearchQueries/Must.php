<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

/**
 * The `must` clause of a BoolQuery. All of the queries must match, and they contribute to the score.
 *
 * Takes one query or a list of them, see BoolClause.
 */
class Must extends BoolClause
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
