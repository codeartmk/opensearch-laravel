<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `value_count` metric aggregation: the number of values of a field across the documents, counting every value of
 * a multi-valued field.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/value-count/
 */
class ValueCount implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The field whose values are counted
     */
    public function __construct(
        private readonly string $field,
    ){}

    /**
     * @param string $field The field whose values are counted
     * @return self
     */
    public static function make(string $field): self
    {
        return new self($field);
    }

    /**
     * @return array{value_count: array{field: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'value_count' => [
                'field' => $this->field,
            ]
        ];
    }
}
