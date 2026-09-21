<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `weighted_avg` metric aggregation: the average of a numeric field, with each document weighted by another
 * numeric field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/weighted-avg/
 */
class WeightedAvg implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $valueField The numeric field to average
     * @param string $weightField The numeric field holding each document's weight
     */
    public function __construct(
        private readonly string $valueField,
        private readonly string $weightField
    ){}

    /**
     * @param string $valueField The numeric field to average
     * @param string $weightField The numeric field holding each document's weight
     * @return self
     */
    public static function make(string $valueField, string $weightField): self
    {
        return new self($valueField, $weightField);
    }

    /**
     * @return array{weighted_avg: array{value: array{field: string}, weight: array{field: string}}}
     */
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
