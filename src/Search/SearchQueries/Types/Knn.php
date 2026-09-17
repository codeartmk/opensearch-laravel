<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

class Knn implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param float[] $vector
     */
    public function __construct(
        private readonly string $field,
        private readonly array $vector,
        private readonly int $k,
        private readonly SearchQueryType|BoolQuery|null $filter
    ){}

    /**
     * @param float[] $vector
     */
    public static function make(string $field, array $vector, int $k, SearchQueryType|BoolQuery|null $filter = null): self
    {
        return new self($field, $vector, $k, $filter);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'knn' => [
                $this->field => [
                    'vector' => $this->vector,
                    'k' => $this->k
                ]
            ]
        ];

        if (!is_null($this->filter)) {
            $query['knn'][$this->field]['filter'] = $this->filter->toOpenSearchQuery();
        }

        return $query;
    }
}
