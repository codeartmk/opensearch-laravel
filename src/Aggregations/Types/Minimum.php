<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Minimum implements OpenSearchQuery, AggregationType
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
            'min' => [
                'field' => $this->field,
            ]
        ];
    }
}