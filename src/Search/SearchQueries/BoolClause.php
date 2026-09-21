<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

/**
 * The shared behaviour of the Must, Should, MustNot and Filter clauses of a BoolQuery.
 *
 * A clause takes either a single query or a list of them, and its output follows the input: a single
 * query is emitted as that query's object, a list as a list of objects (an empty list is sent as `[]`,
 * which OpenSearch accepts). Only the list items are checked; a single argument is guaranteed by its type.
 */
abstract class BoolClause implements OpenSearchQuery
{
    /**
     * @param SearchQueryType|BoolQuery|array<array-key, SearchQueryType|BoolQuery> $queryType One query, or a list of them
     * @throws InvalidSearchParametersException When a list item is neither a SearchQueryType nor a BoolQuery
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
     * A single query is returned as is; a list is returned as a list of queries. BoolQuery puts the
     * result under the clause's key.
     *
     * @return array<string, mixed>|list<array<string, mixed>>
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
