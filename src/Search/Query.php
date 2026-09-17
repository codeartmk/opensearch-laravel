<?php

namespace Codeart\OpensearchLaravel\Search;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

class Query implements OpenSearchQuery
{
    /**
     * @param array{0: SearchQueryType|BoolQuery} $parameters Exactly one root query
     * @throws InvalidSearchParametersException
     */
    public function __construct(
        private readonly array $parameters
    ) {
        if (!count($parameters)) {
            throw new InvalidSearchParametersException('Query requires a root query.');
        }

        // OpenSearch accepts a single root query; conditions are combined inside a BoolQuery instead.
        if (count($parameters) > 1) {
            throw new InvalidSearchParametersException(
                'Query accepts only one root query. Combine conditions in a BoolQuery with Must or Filter clauses.'
            );
        }

        $parameter = reset($parameters);

        if (!$parameter instanceof SearchQueryType && !$parameter instanceof BoolQuery) {
            throw new InvalidSearchParametersException(sprintf(
                'Query accepts only a SearchQueryType or BoolQuery, %s given.',
                get_debug_type($parameter)
            ));
        }
    }

    /**
     * @param array{0: SearchQueryType|BoolQuery} $parameters Exactly one root query
     * @throws InvalidSearchParametersException
     */
    public static function make(array $parameters): self
    {
        return new self($parameters);
    }

    public function toOpenSearchQuery(): array
    {
        $resulting = [];

        foreach ($this->parameters as $parameter) {
            $resulting += [...$parameter->toOpenSearchQuery()];
        }

        return [
            'query' => [
                ...$resulting
            ]
        ];
    }
}
