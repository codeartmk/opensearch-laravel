<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Regexp implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly bool $casInsensitive
    ){}

    public static function make(
        string $field,
        string $value,
        bool $casInsensitive = false
    ): self
    {
        return new self($field, $value, $casInsensitive);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'fuzzy' => [
                $this->field => [
                    'value' => $this->value,
                    'case_insensitive' => $this->casInsensitive
                ]
            ]
        ];
    }
}