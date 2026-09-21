<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `range` query: matches documents whose field value lies within the bounds.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/range/
 */
class Range implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to compare
     * @param array<string, mixed> $ranges The bounds and options, sent as is, e.g. ['gte' => 10, 'lt' => 20] or ['gte'
     *                                     => 'now-1d/d', 'format' => 'strict_date_optional_time']
     */
    public function __construct(
        private readonly string $field,
        private readonly array $ranges
    ){}

    /**
     * @param string $field The field to compare
     * @param array<string, mixed> $ranges The bounds and options, sent as is, e.g. ['gte' => 10, 'lt' => 20] or ['gte'
     *                                     => 'now-1d/d', 'format' => 'strict_date_optional_time']
     * @return self
     */
    public static function make(string $field, array $ranges): self
    {
        return new self($field, $ranges);
    }

    /**
     * @return array{range: array<string, array<string, mixed>>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'range' => [
                $this->field => $this->ranges
            ]
        ];
    }
}