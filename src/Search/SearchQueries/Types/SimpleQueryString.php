<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `simple_query_string` query: a simpler query syntax (+, |, -, quotes, *) that ignores invalid parts instead
 * of failing, so it is safe to pass user input to.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/full-text/simple-query-string/
 */
class SimpleQueryString implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $query The query in simple query string syntax
     * @param list<string>|null $fields The fields to search, optionally boosted, e.g. ['title^2', 'body']. Null leaves
     *                                  it out so OpenSearch's default (the index.query.default_field setting) applies
     * @param string|null $defaultOperator 'OR' or 'AND' between terms without an explicit operator. Null leaves it out
     *                                     so OpenSearch's default (OR) applies
     */
    public function __construct(
        private readonly string $query,
        private readonly ?array $fields,
        private readonly ?string $defaultOperator
    ){}

    /**
     * @param string $query The query in simple query string syntax
     * @param list<string>|null $fields The fields to search, optionally boosted, e.g. ['title^2', 'body']. Null leaves
     *                                  it out so OpenSearch's default (the index.query.default_field setting) applies
     * @param string|null $defaultOperator 'OR' or 'AND' between terms without an explicit operator. Null leaves it out
     *                                     so OpenSearch's default (OR) applies
     * @return self
     */
    public static function make(string $query, ?array $fields = null, ?string $defaultOperator = null): self
    {
        return new self($query, $fields, $defaultOperator);
    }

    /**
     * @return array{simple_query_string: array{query: string, fields?: list<string>, default_operator?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'simple_query_string' => [
                'query' => $this->query
            ]
        ];

        if (!is_null($this->fields)) {
            $query['simple_query_string']['fields'] = $this->fields;
        }

        if (!is_null($this->defaultOperator)) {
            $query['simple_query_string']['default_operator'] = $this->defaultOperator;
        }

        return $query;
    }
}
