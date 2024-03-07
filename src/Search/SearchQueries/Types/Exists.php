<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Exists implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
    ){}

    public static function make(string $field): self
    {
        return new self($field);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'exists' => [
                'field' => $this->field
            ]
        ];
    }
}