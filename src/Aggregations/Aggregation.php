<?php

namespace Codeart\OpensearchLaravel\Aggregations;

use Codeart\OpensearchLaravel\Aggregations\Types\AggregationType;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A named aggregation: the name OpenSearch reports its result under, the aggregation type, and optional
 * sub-aggregations that run inside each of its buckets.
 *
 * @see https://opensearch.org/docs/latest/aggregations/
 */
class Aggregation implements OpenSearchQuery
{
    /**
     * @param string $name The name the result is returned under in the response's `aggregations`
     * @param AggregationType $aggregationType The aggregation to run, e.g. Terms::make('category')
     * @param Aggregation|array<array-key, Aggregation>|null $aggregation One sub-aggregation, or several siblings, sent
     *                                                                    under `aggs`. They are validated when this
     *                                                                    aggregation is built, not here. Null or an
     *                                                                    empty array leaves `aggs` out
     */
    public function __construct(
        private readonly string $name,
        private readonly AggregationType $aggregationType,
        private readonly Aggregation|array|null $aggregation
    ){}

    /**
     * @param string $name The name the result is returned under in the response's `aggregations`
     * @param AggregationType $aggregationType The aggregation to run, e.g. Terms::make('category')
     * @param Aggregation|array<array-key, Aggregation>|null $aggregation One sub-aggregation, or several siblings, sent
     *                                                                    under `aggs`. They are validated when this
     *                                                                    aggregation is built, not here. Null or an
     *                                                                    empty array leaves `aggs` out
     * @return self
     */
    public static function make(string $name, AggregationType $aggregationType, Aggregation|array|null $aggregation = null): self
    {
        return new self($name, $aggregationType, $aggregation);
    }

    /**
     * Sub-aggregations go through AggregationBuilder, so they are validated here.
     *
     * @return array<string, array<string, mixed>> The name => the aggregation body, plus `aggs` when set
     * @throws \Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException When a sub-aggregation
     *         list is empty, has an item that isn't an Aggregation, or has two aggregations with the same name at one
     *         level
     */
    public function toOpenSearchQuery(): array
    {
        return [
            $this->name => [
                ...$this->aggregationType->toOpenSearchQuery(),
                ...(!empty($this->aggregation) ? (new AggregationBuilder($this->aggregation))->toOpenSearchQuery() : [])
            ]
        ];
    }
}
