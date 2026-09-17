<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

class FunctionScore implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly array $functions,
        private readonly SearchQueryType|BoolQuery|null $query,
        private readonly ?string $scoreMode,
        private readonly ?string $boostMode,
        private readonly int|float|null $maxBoost,
        private readonly int|float|null $minScore
    ){}

    /**
     * @param array $functions The function definitions, e.g. ['field_value_factor' => [...], 'weight' => 2].
     *                         A `filter` may be given as a query object instead of an array.
     */
    public static function make(
        array $functions,
        SearchQueryType|BoolQuery|null $query = null,
        ?string $scoreMode = null,
        ?string $boostMode = null,
        int|float|null $maxBoost = null,
        int|float|null $minScore = null
    ): self
    {
        return new self($functions, $query, $scoreMode, $boostMode, $maxBoost, $minScore);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'function_score' => [
                'functions' => array_map(function (array $function) {
                    if (isset($function['filter']) && $function['filter'] instanceof OpenSearchQuery) {
                        $function['filter'] = $function['filter']->toOpenSearchQuery();
                    }

                    return $function;
                }, array_values($this->functions))
            ]
        ];

        if (!is_null($this->query)) {
            $query['function_score']['query'] = $this->query->toOpenSearchQuery();
        }

        if (!is_null($this->scoreMode)) {
            $query['function_score']['score_mode'] = $this->scoreMode;
        }

        if (!is_null($this->boostMode)) {
            $query['function_score']['boost_mode'] = $this->boostMode;
        }

        if (!is_null($this->maxBoost)) {
            $query['function_score']['max_boost'] = $this->maxBoost;
        }

        if (!is_null($this->minScore)) {
            $query['function_score']['min_score'] = $this->minScore;
        }

        return $query;
    }
}
