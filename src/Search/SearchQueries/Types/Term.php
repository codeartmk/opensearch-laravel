<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Term implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly string|bool|int $value
    ){}

    public static function make(string $field, string|bool|int $value): self
    {
        return new self($field, $value);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'term' => [
                $this->field => $this->value
            ]
        ];
    }
}
