<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Terms implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly string|int|array $values,
    ){}

    public static function make(string $field, string|int|array $values): self
    {
        return new self($field, $values);
    }

    public function toOpenSearchQuery(): array
    {
        if(is_array($this->values)) {
            $values = $this->values;
        } else {
            $values = [$this->values];
        }

        return [
            'terms' =>  [
                $this->field => $values
            ]
        ];
    }
}