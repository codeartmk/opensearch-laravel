<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Range implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly array $ranges,
    ){}

    public static function make(string $field, array $ranges): self
    {
        return new self($field, $ranges);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'range' => [
                'field' => $this->field,
                'ranges' => $this->ranges
            ]
        ];
    }
}