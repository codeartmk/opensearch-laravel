<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `terms_set` query: matches documents that contain a minimum number of the terms. That number is read from
 * a field or computed by a script, so pass one of the two.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/terms-set/
 */
class TermsSet implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param list<string|int> $terms The terms to look for
     * @param string|null $minimumShouldMatchField A numeric field holding the number of terms that must match. Null
     *                                             leaves it out
     * @param string|null $minimumShouldMatchScript A script source returning the number of terms that must match, e.g.
     *                                              'Math.min(params.num_terms, 2)'. Null leaves it out
     */
    public function __construct(
        private readonly string $field,
        private readonly array $terms,
        private readonly ?string $minimumShouldMatchField,
        private readonly ?string $minimumShouldMatchScript
    ){}

    /**
     * @param string $field The field to search
     * @param list<string|int> $terms The terms to look for
     * @param string|null $minimumShouldMatchField A numeric field holding the number of terms that must match. Null
     *                                             leaves it out
     * @param string|null $minimumShouldMatchScript A script source returning the number of terms that must match, e.g.
     *                                              'Math.min(params.num_terms, 2)'. Null leaves it out
     * @return self
     */
    public static function make(
        string $field,
        array $terms,
        ?string $minimumShouldMatchField = null,
        ?string $minimumShouldMatchScript = null
    ): self
    {
        return new self($field, $terms, $minimumShouldMatchField, $minimumShouldMatchScript);
    }

    /**
     * @return array{terms_set: array<string, array{terms: list<string|int>, minimum_should_match_field?: string, minimum_should_match_script?: array{source: string}}>}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'terms_set' => [
                $this->field => [
                    'terms' => $this->terms
                ]
            ]
        ];

        if (!is_null($this->minimumShouldMatchField)) {
            $query['terms_set'][$this->field]['minimum_should_match_field'] = $this->minimumShouldMatchField;
        }

        if (!is_null($this->minimumShouldMatchScript)) {
            $query['terms_set'][$this->field]['minimum_should_match_script'] = [
                'source' => $this->minimumShouldMatchScript
            ];
        }

        return $query;
    }
}
