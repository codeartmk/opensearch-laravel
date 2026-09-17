<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Histogram implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly int|float $interval,
        private readonly ?int $minDocCount
    ){}

    public static function make(string $field, int|float $interval, ?int $minDocCount = null): self
    {
        return new self($field, $interval, $minDocCount);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'histogram' => [
                'field' => $this->field,
                'interval' => $this->interval,
            ]
        ];

        if (!is_null($this->minDocCount)) {
            $query['histogram']['min_doc_count'] = $this->minDocCount;
        }

        return $query;
    }
}
