<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `multi_match` query: runs a match query across several fields.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/full-text/multi-match/
 */
class MultiMatch implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $query The text to search for
     * @param list<string> $fields The fields to search, optionally boosted, e.g. ['title^2', 'description']
     * @param string|null $type How the fields are combined: best_fields, most_fields, cross_fields, phrase,
     *                          phrase_prefix or bool_prefix. Null leaves it out so OpenSearch's default (best_fields)
     *                          applies
     * @param string|null $operator 'or' or 'and' between the analyzed terms. Null leaves it out so OpenSearch's default
     *                              (or) applies
     */
    public function __construct(
        private readonly string $query,
        private readonly array $fields,
        private readonly ?string $type,
        private readonly ?string $operator
    ){}

    /**
     * @param string $query The text to search for
     * @param list<string> $fields The fields to search, optionally boosted, e.g. ['title^2', 'description']
     * @param string|null $type How the fields are combined: best_fields, most_fields, cross_fields, phrase,
     *                          phrase_prefix or bool_prefix. Null leaves it out so OpenSearch's default (best_fields)
     *                          applies
     * @param string|null $operator 'or' or 'and' between the analyzed terms. Null leaves it out so OpenSearch's default
     *                              (or) applies
     * @return self
     */
    public static function make(string $query, array $fields, ?string $type = null, ?string $operator = null): self
    {
        return new self($query, $fields, $type, $operator);
    }

    /**
     * @return array{multi_match: array{query: string, fields: list<string>, type?: string, operator?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'multi_match' => [
                'query' => $this->query,
                'fields' => $this->fields
            ]
        ];

        if (!is_null($this->type)) {
            $query['multi_match']['type'] = $this->type;
        }

        if (!is_null($this->operator)) {
            $query['multi_match']['operator'] = $this->operator;
        }

        return $query;
    }
}
