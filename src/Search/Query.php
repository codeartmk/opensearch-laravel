<?php

namespace Codeart\OpensearchLaravel\Search;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Query implements OpenSearchQuery
{
    public function __construct(
        private array $parameters
    ){}

    public static function make(array $parameters): self
    {
        return new self($parameters);
    }

    public function toOpenSearchQuery(): array
    {
        $resulting = [];

        foreach ($this->parameters as $parameter) {
            $resulting += [...$parameter->toOpenSearchQuery()];
        }

        return [
            'query' => [
                ...$resulting
            ]
        ];
    }
}
