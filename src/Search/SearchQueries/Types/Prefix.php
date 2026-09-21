<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `prefix` query: matches terms that start with the value.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/prefix/
 */
class Prefix implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string $value The prefix; it is not analyzed
     * @param bool $caseInsensitive Whether to match regardless of case. Always sent; false is also OpenSearch's default
     */
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly bool $caseInsensitive
    ){}

    /**
     * @param string $field The field to search
     * @param string $value The prefix; it is not analyzed
     * @param bool $caseInsensitive Whether to match regardless of case. Always sent; false is also OpenSearch's default
     * @return self
     */
    public static function make(string $field, string $value, bool $caseInsensitive = false): self
    {
        return new self($field, $value, $caseInsensitive);
    }

    /**
     * @return array{prefix: array<string, array{value: string, case_insensitive: bool}>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'prefix' => [
                $this->field => [
                    'value' => $this->value,
                    'case_insensitive' => $this->caseInsensitive
                ]
            ]
        ];
    }
}