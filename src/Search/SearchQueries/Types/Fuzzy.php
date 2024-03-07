<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Fuzzy implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly int|string $fuzziness,
        private readonly int $maxExpansions,
        private readonly int $prefixLength,
        private readonly bool $transpositions
    ){}

    public static function make(
        string $field,
        string $value,
        int|string $fuzziness = 'AUTO',
        int $maxExpansions = 50,
        int $prefixLength = 0,
        bool $transpositions = true
    ): self
    {
        return new self($field, $value, $fuzziness, $maxExpansions, $prefixLength, $transpositions);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'fuzzy' => [
                $this->field => [
                    'value' => $this->value,
                    'fuzziness' => $this->fuzziness,
                    'max_expansions' => $this->maxExpansions,
                    'prefix_length' => $this->prefixLength,
                    'transpositions' => $this->transpositions
                ]
            ]
        ];
    }
}