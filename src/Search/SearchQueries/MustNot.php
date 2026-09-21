<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

/**
 * The `must_not` clause of a BoolQuery. None of the queries may match. Runs in filter context, so it doesn't affect the score.
 *
 * Takes one query or a list of them, see BoolClause.
 */
class MustNot extends BoolClause
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
