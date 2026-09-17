<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MultiMatch implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $query,
        private readonly array $fields,
        private readonly ?string $type,
        private readonly ?string $operator
    ){}

    public static function make(string $query, array $fields, ?string $type = null, ?string $operator = null): self
    {
        return new self($query, $fields, $type, $operator);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'multi_match' => [
                'query' => $this->query,
                'fields' => $this->fields
            ]
        ];

        if (!is_null($this->type)) {
            $query['multi_match']['type'] = $this->type;
        }

        if (!is_null($this->operator)) {
            $query['multi_match']['operator'] = $this->operator;
        }

        return $query;
    }
}
