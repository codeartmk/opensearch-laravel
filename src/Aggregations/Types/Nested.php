<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Nested implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $path,
    ){}

    public static function make(string $path): self
    {
        return new self($path);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'nested' => [
                'path' => $this->path,
            ]
        ];
    }
}
