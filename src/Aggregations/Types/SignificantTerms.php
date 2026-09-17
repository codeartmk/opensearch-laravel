<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class SignificantTerms implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly ?int $size
    ){}

    public static function make(string $field, ?int $size = null): self
    {
        return new self($field, $size);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'significant_terms' => [
                'field' => $this->field,
            ]
        ];

        if (!is_null($this->size)) {
            $query['significant_terms']['size'] = $this->size;
        }

        return $query;
    }
}
