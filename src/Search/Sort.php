<?php

namespace Codeart\OpensearchLaravel\Search;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Sort implements OpenSearchQuery
{
    public function __construct(
        private readonly array $parameters
    ){}

    public static function make(array $parameters): self
    {
        return new self($parameters);
    }


    public function toOpenSearchQuery(): array
    {
        return [
            'sort' => [
                ...$this->parameters
            ]
        ];
    }
}
