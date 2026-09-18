<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `fuzzy` query: matches terms within an edit distance of the value.
 * Unlike the other nodes it always sends its optional parameters; their defaults are OpenSearch's own.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/term/fuzzy/
 */
class Fuzzy implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The field to search
     * @param string $value The term to match; it is not analyzed
     * @param int|string $fuzziness The allowed edit distance, or 'AUTO' to base it on the term length
     * @param int $maxExpansions The maximum number of terms the value may expand to
     * @param int $prefixLength The number of leading characters that must match exactly
     * @param bool $transpositions Whether swapping two adjacent characters counts as a single edit
     */
    public function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly int|string $fuzziness,
        private readonly int $maxExpansions,
        private readonly int $prefixLength,
        private readonly bool $transpositions
    ){}

    /**
     * @param string $field The field to search
     * @param string $value The term to match; it is not analyzed
     * @param int|string $fuzziness The allowed edit distance, or 'AUTO' to base it on the term length
     * @param int $maxExpansions The maximum number of terms the value may expand to
     * @param int $prefixLength The number of leading characters that must match exactly
     * @param bool $transpositions Whether swapping two adjacent characters counts as a single edit
     * @return self
     */
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

    /**
     * @return array{fuzzy: array<string, array{value: string, fuzziness: int|string, max_expansions: int, prefix_length: int, transpositions: bool}>}
     */
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