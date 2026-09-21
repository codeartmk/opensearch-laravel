<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `wildcard` query: matches terms against a pattern where `*` stands for any characters and `?` for one.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/wildcard/
 */
class Wildcard implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string $value The pattern; it is not analyzed
     * @param bool $caseInsensitive Whether to match regardless of case. Always sent; false is also OpenSearch's default
     */
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly bool $caseInsensitive
    ){}

    /**
     * @param string $field The field to search
     * @param string $value The pattern; it is not analyzed
     * @param bool $caseInsensitive Whether to match regardless of case. Always sent; false is also OpenSearch's default
     * @return self
     */
    public static function make(string $field, string $value, bool $caseInsensitive = false): self
    {
        return new self($field, $value, $caseInsensitive);
    }

    /**
     * @return array{wildcard: array<string, array{value: string, case_insensitive: bool}>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'wildcard' => [
                $this->field => [
                    'value' => $this->value,
                    'case_insensitive' => $this->caseInsensitive
                ]
            ]
        ];
    }
}