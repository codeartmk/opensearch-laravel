<?php

namespace Codeart\OpensearchLaravel\Aggregations;

use Codeart\OpensearchLaravel\Aggregations\Types\AggregationType;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Aggregation implements OpenSearchQuery
{
    public function __construct(
        private readonly string $name,
        private readonly AggregationType $aggregationType,
        private readonly ?Aggregation $aggregation
    ){}

    public static function make(string $name, AggregationType $aggregationType, ?Aggregation $aggregation = null): self
    {
        return new self($name, $aggregationType, $aggregation);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            $this->name => [
                ...$this->aggregationType->toOpenSearchQuery(),
                ...(!is_null($this->aggregation) ? ["aggs" => $this->aggregation->toOpenSearchQuery()] : [])
            ]
        ];
    }
}
