<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Range implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly bool $caseInsensitive
    ){}

    public static function make(string $field, string $value, bool $caseInsensitive = false): self
    {
        return new self($field, $value, $caseInsensitive);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'prefix' => [
                $this->field => [
                    'value' => $this->value,
                    'case_insensitive' => $this->caseInsensitive
                ]
            ]
        ];
    }
}