<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

/**
 * A `knn` query (k-NN plugin): finds the nearest neighbours of the vector in a knn_vector field.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/specialized/k-nn/index/
 */
class Knn implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The knn_vector field
     * @param float[] $vector The query vector; its length must match the field's dimension
     * @param int $k The number of nearest neighbours to find
     * @param SearchQueryType|BoolQuery|null $filter Restricts the candidates to documents matching this query. Null
     *                                               leaves it out, so every document is a candidate
     */
    public function __construct(
        private readonly string $field,
        private readonly array $vector,
        private readonly int $k,
        private readonly SearchQueryType|BoolQuery|null $filter
    ){}

    /**
     * @param string $field The knn_vector field
     * @param float[] $vector The query vector; its length must match the field's dimension
     * @param int $k The number of nearest neighbours to find
     * @param SearchQueryType|BoolQuery|null $filter Restricts the candidates to documents matching this query. Null
     *                                               leaves it out, so every document is a candidate
     * @return self
     */
    public static function make(string $field, array $vector, int $k, SearchQueryType|BoolQuery|null $filter = null): self
    {
        return new self($field, $vector, $k, $filter);
    }

    /**
     * @return array{knn: array<string, array{vector: float[], k: int, filter?: array<string, mixed>}>}
     */
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
