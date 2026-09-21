<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `terms` query: matches documents whose field contains any of the exact values.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/terms/
 */
class Terms implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string|int|list<string|int> $values One value, or a list of values; a single value is sent as a one-item
     *                                            list
     */
    public function __construct(
        private readonly string $field,
        private readonly string|int|array $values,
    ){}

    /**
     * @param string $field The field to search
     * @param string|int|list<string|int> $values One value, or a list of values; a single value is sent as a one-item
     *                                            list
     * @return self
     */
    public static function make(string $field, string|int|array $values): self
    {
        return new self($field, $values);
    }

    /**
     * @return array{terms: array<string, list<string|int>>}
     */
    public function toOpenSearchQuery(): array
    {
        if(is_array($this->values)) {
            $values = $this->values;
        } else {
            $values = [$this->values];
        }

        return [
            'terms' =>  [
                $this->field => $values
            ]
        ];
    }
}