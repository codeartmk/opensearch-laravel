<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

/**
 * The `should` clause of a BoolQuery. The queries should match: each match raises the score. With no must or filter clause, at least one has to match unless `minimum_should_match` says otherwise.
 *
 * Takes one query or a list of them, see BoolClause.
 */
class Should extends BoolClause
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
