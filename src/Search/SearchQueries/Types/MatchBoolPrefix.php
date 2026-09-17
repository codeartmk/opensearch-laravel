<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MatchBoolPrefix implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly string $value
    ){}

    public static function make(string $field, string $value): self
    {
        return new self($field, $value);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'match_bool_prefix' => [
                $this->field => $this->value
            ]
        ];
    }
}
