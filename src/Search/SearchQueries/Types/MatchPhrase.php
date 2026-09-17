<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MatchPhrase implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly ?int $slop
    ){}

    public static function make(string $field, string $value, ?int $slop = null): self
    {
        return new self($field, $value, $slop);
    }

    public function toOpenSearchQuery(): array
    {
        if (is_null($this->slop)) {
            return [
                'match_phrase' => [
                    $this->field => $this->value
                ]
            ];
        }

        return [
            'match_phrase' => [
                $this->field => [
                    'query' => $this->value,
                    'slop' => $this->slop
                ]
            ]
        ];
    }
}
