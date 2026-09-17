<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Terms implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly int $size,
        private readonly ?array $order = null,
        private readonly ?int $minDocCount = null,
    ){}

    public static function make(string $field, int $size = 10, ?array $order = null, ?int $minDocCount = null): self
    {
        return new self($field, $size, $order, $minDocCount);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'terms' => [
                'field' => $this->field,
                'size' => $this->size
            ]
        ];

        if (!is_null($this->order)) {
            $query['terms']['order'] = $this->order;
        }

        if (!is_null($this->minDocCount)) {
            $query['terms']['min_doc_count'] = $this->minDocCount;
        }

        return $query;
    }
}
