<?php

namespace Codeart\OpensearchLaravel\Aggregations;

use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * The `aggs` part of the request body, holding one aggregation or several siblings.
 * OpenSearchBuilder::aggregations() and Aggregation's sub-aggregations both go through it.
 *
 * The constructor builds every aggregation, and so every sub-aggregation level, to validate it, so errors surface
 * when the builder is created rather than when the request is sent.
 *
 * @see https://opensearch.org/docs/latest/aggregations/
 */
class AggregationBuilder implements OpenSearchQuery
{
    /**
     * @param Aggregation|array<array-key, Aggregation> $aggregations One aggregation, or several siblings
     * @throws InvalidAggregationParametersException When the list is empty, has an item that isn't an Aggregation, or
     *                                               has two aggregations with the same name at one level, at this level
     *                                               or any sub-aggregation level
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

    /**
     * @return array{aggs: array<string, array<string, mixed>>}
     */
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
