<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * An `ids` query: matches documents by their `_id`.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/ids/
 */
class Ids implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param int|array<array-key, int|string> $value One id, or a list of ids; a single id is sent as a one-item list.
     *                                                A single string id has to be passed in an array
     */
    public function __construct(
        private readonly int|array $value,
    ){}

    /**
     * @param int|array<array-key, int|string> $value One id, or a list of ids; a single id is sent as a one-item list.
     *                                                A single string id has to be passed in an array
     * @return self
     */
    public static function make(int|array $value): self
    {
        return new self($value);
    }

    /**
     * @return array{ids: array{values: array<array-key, int|string>}}
     */
    public function toOpenSearchQuery(): array
    {
        if(is_array($this->value)) {
            $values = $this->value;
        } else {
            $values = [$this->value];
        }

        return [
            'ids' => [
                'values' => $values
            ]
        ];
    }
}