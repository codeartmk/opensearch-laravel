<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Ids implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly int|array $value,
    ){}

    public static function make(string $value): self
    {
        return new self($value);
    }

    public function toOpenSearchQuery(): array
    {
        if(is_array($this->value)) {
            $values = $this->value;
        } else {
            $values = [$this->value];
        }

        return [
            'values' =>  $values
        ];
    }
}