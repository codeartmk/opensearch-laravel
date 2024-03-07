<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;

class BoolQuery implements OpenSearchQuery
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
        $resulting = [
            'bool' => []
        ];

        foreach ($this->parameters as $parameter) {
            if($parameter instanceof Must) {
                $resulting['bool']['must'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof Should) {
                $resulting['bool']['should'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof MustNot) {
                $resulting['bool']['must_not'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof Filter) {
                $resulting['bool']['filter'] = $parameter->toOpenSearchQuery();
            }
        }

        if(
            isset($this->parameters['minimum_should_match'])
            && isset($resulting['bool']['should'])
        ) {
            $resulting['bool']['minimum_should_match'] = $this->parameters['minimum_should_match'];
        }

        if(
            isset($this->parameters['boost'])
        ) {
            $resulting['bool']['boost'] = $this->parameters['boost'];
        }

        return $resulting;
    }
}
