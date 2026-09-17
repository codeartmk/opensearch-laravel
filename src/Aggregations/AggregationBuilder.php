<?php

namespace Codeart\OpensearchLaravel\Aggregations;

use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class AggregationBuilder implements OpenSearchQuery
{
    /**
     * @param Aggregation|Aggregation[] $aggregations
     * @throws InvalidAggregationParametersException
     */
    public function __construct(
        private readonly Aggregation|array $aggregations
    ) {
        if (is_array($aggregations) && !count($aggregations)) {
            throw new InvalidAggregationParametersException('Too few parameters to aggregation method. At least one Aggregation required.');
        }

        $names = [];

        foreach (is_array($aggregations) ? $aggregations : [$aggregations] as $aggregation) {
            if (!$aggregation instanceof Aggregation) {
                throw new InvalidAggregationParametersException(sprintf(
                    'Aggregations must be Aggregation instances, %s given.',
                    get_debug_type($aggregation)
                ));
            }

            // Building the aggregation also validates its sub-aggregations, so every level fails here, not at get().
            $name = array_key_first($aggregation->toOpenSearchQuery());

            if (isset($names[$name])) {
                throw new InvalidAggregationParametersException("Aggregation names must be unique, \"$name\" is used more than once.");
            }

            $names[$name] = true;
        }
    }

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
