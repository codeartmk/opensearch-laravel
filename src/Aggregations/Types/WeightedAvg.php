<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class WeightedAvg implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $valueField,
        private readonly string $weightField
    ){}

    public static function make(string $valueField, string $weightField): self
    {
        return new self($valueField, $weightField);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'weighted_avg' => [
                'value' => ['field' => $this->valueField],
                'weight' => ['field' => $this->weightField],
            ]
        ];
    }
}
