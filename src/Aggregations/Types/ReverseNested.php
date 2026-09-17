<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class ReverseNested implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly ?string $path
    ){}

    public static function make(?string $path = null): self
    {
        return new self($path);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'reverse_nested' => is_null($this->path) ? new \stdClass() : ['path' => $this->path]
        ];
    }
}
