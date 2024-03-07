<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Range implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly array $ranges
    ){}

    public static function make(string $field, array $ranges): self
    {
        return new self($field, $ranges);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'prefix' => [
                $this->field => $this->ranges
            ]
        ];
    }
}