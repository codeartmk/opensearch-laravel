<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

/**
 * The shared behaviour of the Must, Should, MustNot and Filter clauses of a BoolQuery.
 */
abstract class BoolClause implements OpenSearchQuery
{
    /**
     * @param SearchQueryType|BoolQuery|array<SearchQueryType|BoolQuery> $queryType
     * @throws InvalidSearchParametersException
     */
    public function __construct(
        private readonly SearchQueryType|BoolQuery|array $queryType
    ) {
        if (!is_array($queryType)) {
            return;
        }

        foreach ($queryType as $query) {
            if (!$query instanceof SearchQueryType && !$query instanceof BoolQuery) {
                throw new InvalidSearchParametersException(sprintf(
                    '%s accepts only SearchQueryType or BoolQuery items, %s given.',
                    class_basename(static::class),
                    get_debug_type($query)
                ));
            }
        }
    }

    /**
     * A single query is returned as is; a list is returned as a list of queries.
     */
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
