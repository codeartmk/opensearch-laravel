<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `regexp` query: matches terms against a regular expression.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/regexp/
 */
class Regexp implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string $value The regular expression, in Lucene syntax; it is not analyzed
     * @param bool $casInsensitive Whether to match regardless of case. Always sent; false is also OpenSearch's default.
     *                             Note the spelling: Prefix and Wildcard call it caseInsensitive
     */
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly bool $casInsensitive
    ){}

    /**
     * @param string $field The field to search
     * @param string $value The regular expression, in Lucene syntax; it is not analyzed
     * @param bool $casInsensitive Whether to match regardless of case. Always sent; false is also OpenSearch's default.
     *                             Note the spelling: Prefix and Wildcard call it caseInsensitive
     * @return self
     */
    public static function make(
        string $field,
        string $value,
        bool $casInsensitive = false
    ): self
    {
        return new self($field, $value, $casInsensitive);
    }

    /**
     * @return array{regexp: array<string, array{value: string, case_insensitive: bool}>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'regexp' => [
                $this->field => [
                    'value' => $this->value,
                    'case_insensitive' => $this->casInsensitive
                ]
            ]
        ];
    }
}