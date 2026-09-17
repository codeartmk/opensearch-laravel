<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class PercentileRanks implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly array $values
    ){}

    public static function make(string $field, array $values): self
    {
        return new self($field, $values);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'percentile_ranks' => [
                'field' => $this->field,
                'values' => $this->values,
            ]
        ];
    }
}
