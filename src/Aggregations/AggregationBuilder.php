<?php

namespace Codeart\OpensearchLaravel\Aggregations;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class AggregationBuilder implements OpenSearchQuery
{
    /**
     * @param Aggregation|Aggregation[] $aggregations
     */
    public function __construct(
        private readonly Aggregation|array $aggregations
    ){}

    public function toOpenSearchQuery(): array
    {
        if(!is_array($this->aggregations)) {
            return [
                'aggs' => [
                    ...$this->aggregations->toOpenSearchQuery()
                ]
            ];
        }

        $aggregations = [];

        foreach ($this->aggregations as $aggregation) {
            $aggregations += $aggregation->toOpenSearchQuery();
        }

        return [
            'aggs' => $aggregations
        ];
    }
}
