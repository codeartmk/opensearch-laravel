<?php

namespace Codeart\OpensearchLaravel\Aggregations;

use Codeart\OpensearchLaravel\Aggregations\Types\AggregationType;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Aggregation implements OpenSearchQuery
{
    /**
     * @param Aggregation|Aggregation[]|null $aggregation
     */
    public function __construct(
        private readonly string $name,
        private readonly AggregationType $aggregationType,
        private readonly Aggregation|array|null $aggregation
    ){}

    /**
     * @param Aggregation|Aggregation[]|null $aggregation
     */
    public static function make(string $name, AggregationType $aggregationType, Aggregation|array|null $aggregation = null): self
    {
        return new self($name, $aggregationType, $aggregation);
    }

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
