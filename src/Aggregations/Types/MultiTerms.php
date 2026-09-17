<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MultiTerms implements OpenSearchQuery, AggregationType
{
    /**
     * @param string[] $fields
     */
    public function __construct(
        private readonly array $fields,
        private readonly int $size
    ){}

    /**
     * @param string[] $fields
     */
    public static function make(array $fields, int $size = 10): self
    {
        return new self($fields, $size);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'multi_terms' => [
                'terms' => array_map(fn(string $field) => ['field' => $field], array_values($this->fields)),
                'size' => $this->size,
            ]
        ];
    }
}
