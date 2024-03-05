<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MatchPhrasePrefix implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private string $field,
        private string|bool|int|array $value
    ){}

    public static function make(string $field, string|bool|int|array $value): self
    {
        return new self($field, $value);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'match_phrase_prefix' => [
                $this->field => $this->value
            ]
        ];
    }
}
