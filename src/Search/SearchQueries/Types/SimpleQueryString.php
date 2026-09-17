<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class SimpleQueryString implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $query,
        private readonly ?array $fields,
        private readonly ?string $defaultOperator
    ){}

    public static function make(string $query, ?array $fields = null, ?string $defaultOperator = null): self
    {
        return new self($query, $fields, $defaultOperator);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'simple_query_string' => [
                'query' => $this->query
            ]
        ];

        if (!is_null($this->fields)) {
            $query['simple_query_string']['fields'] = $this->fields;
        }

        if (!is_null($this->defaultOperator)) {
            $query['simple_query_string']['default_operator'] = $this->defaultOperator;
        }

        return $query;
    }
}
