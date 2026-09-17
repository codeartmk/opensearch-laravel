<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class TermsSet implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly array $terms,
        private readonly ?string $minimumShouldMatchField,
        private readonly ?string $minimumShouldMatchScript
    ){}

    public static function make(
        string $field,
        array $terms,
        ?string $minimumShouldMatchField = null,
        ?string $minimumShouldMatchScript = null
    ): self
    {
        return new self($field, $terms, $minimumShouldMatchField, $minimumShouldMatchScript);
    }

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
