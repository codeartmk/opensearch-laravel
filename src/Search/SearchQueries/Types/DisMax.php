<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

class DisMax implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param array<SearchQueryType|BoolQuery> $queries
     */
    public function __construct(
        private readonly array $queries,
        private readonly int|float|null $tieBreaker
    ){}

    /**
     * @param array<SearchQueryType|BoolQuery> $queries
     */
    public static function make(array $queries, int|float|null $tieBreaker = null): self
    {
        return new self($queries, $tieBreaker);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'dis_max' => [
                'queries' => array_map(fn($query) => $query->toOpenSearchQuery(), array_values($this->queries))
            ]
        ];

        if (!is_null($this->tieBreaker)) {
            $query['dis_max']['tie_breaker'] = $this->tieBreaker;
        }

        return $query;
    }
}
