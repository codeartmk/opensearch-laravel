<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Terms implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly int $size
    ){}

    public static function make(string $field, int $size = 10): self
    {
        return new self($field, $size);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'terms' => [
                'field' => $this->field,
                'size' => $this->size
            ]
        ];
    }
}
