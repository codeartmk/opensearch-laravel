<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `multi_terms` bucket aggregation: one bucket per combination of the values of several fields, ordered by
 * document count.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/multi-terms/
 */
class MultiTerms implements OpenSearchQuery, AggregationType
{
    /**
     * @param array<array-key, string> $fields The fields to combine, in order, at least two; the keys are dropped
     * @param int $size The number of buckets to return. Always sent; 10 is also OpenSearch's default
     * @throws InvalidAggregationParametersException When the list has fewer than two fields or an item that is not a
     *                                               string
     */
    public function __construct(
        private readonly array $fields,
        private readonly int $size
    ){
        // OpenSearch rejects fewer than two terms: "multi term aggregation must has at least 2 terms".
        if (count($fields) < 2) {
            throw new InvalidAggregationParametersException(sprintf(
                'MultiTerms requires at least two fields, %d given; use Terms for a single field.',
                count($fields)
            ));
        }

        foreach ($fields as $key => $field) {
            if (!is_string($field)) {
                throw new InvalidAggregationParametersException(sprintf(
                    'MultiTerms accepts only field names (strings) as fields, %s given at key %s.',
                    get_debug_type($field),
                    var_export($key, true)
                ));
            }
        }
    }

    /**
     * @param array<array-key, string> $fields The fields to combine, in order, at least two; the keys are dropped
     * @param int $size The number of buckets to return. Always sent; 10 is also OpenSearch's default
     * @return self
     * @throws InvalidAggregationParametersException When the list has fewer than two fields or an item that is not a
     *                                               string
     */
    public static function make(array $fields, int $size = 10): self
    {
        return new self($fields, $size);
    }

    /**
     * @return array{multi_terms: array{terms: list<array{field: string}>, size: int}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'multi_terms' => [
                'terms' => array_map(fn(string $field) => ['field' => $field], array_values($this->fields)),
                'size' => $this->size,
            ]
        ];
    }
}
