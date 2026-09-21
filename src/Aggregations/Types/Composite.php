<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `composite` bucket aggregation: buckets for every combination of the values of several sources, returned a page
 * at a time. Pass the previous response's `after_key` as `after` to get the next page.
 *
 * A string source is shorthand for a `terms` source on that field. Histogram and DateHistogram objects and raw
 * source arrays work too. Don't pass the Terms aggregation object as a source: it always sends `size`, which
 * OpenSearch rejects inside a composite source ("[terms] unknown field [size]"); use the string shorthand or a raw
 * array instead.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/composite/
 */
class Composite implements OpenSearchQuery, AggregationType
{
    /**
     * @param non-empty-array<string, string|AggregationType|array<string, mixed>> $sources Source name => field name
     *        (a terms source), aggregation such as Histogram or DateHistogram, or raw source array. The order of the
     *        sources is kept
     * @param int $size The number of buckets per page. Always sent; 10 is also OpenSearch's default
     * @param array<string, mixed>|null $after The `after_key` of the previous page, source name => value. Null leaves
     *                                         it out so the first page is returned
     * @throws InvalidAggregationParametersException When the list is empty or has a source that is not a string, an
     *                                               array or an aggregation (AggregationType and OpenSearchQuery)
     */
    public function __construct(
        private readonly array $sources,
        private readonly int $size,
        private readonly ?array $after
    ){
        // OpenSearch rejects "sources": [] ("Failed to build [composite] after last required field arrived").
        if (!count($sources)) {
            throw new InvalidAggregationParametersException('Composite requires at least one source.');
        }

        foreach ($sources as $name => $source) {
            if (!is_string($source) && !is_array($source)
                && !($source instanceof AggregationType && $source instanceof OpenSearchQuery)) {
                throw new InvalidAggregationParametersException(sprintf(
                    'Composite accepts only a field name, an aggregation or an array as a source, %s given for %s.',
                    get_debug_type($source),
                    var_export($name, true)
                ));
            }
        }
    }

    /**
     * @param non-empty-array<string, string|AggregationType|array<string, mixed>> $sources Source name => field name
     *        (a terms source), aggregation such as Histogram or DateHistogram, or raw source array. The order of the
     *        sources is kept
     * @param int $size The number of buckets per page. Always sent; 10 is also OpenSearch's default
     * @param array<string, mixed>|null $after The `after_key` of the previous page, source name => value. Null leaves
     *                                         it out so the first page is returned
     * @return self
     * @throws InvalidAggregationParametersException When the list is empty or has a source that is not a string, an
     *                                               array or an aggregation (AggregationType and OpenSearchQuery)
     */
    public static function make(array $sources, int $size = 10, ?array $after = null): self
    {
        return new self($sources, $size, $after);
    }

    /**
     * @return array{composite: array{sources: list<array<string, array<string, mixed>>>, size: int, after?: array<string, mixed>}}
     */
    public function toOpenSearchQuery(): array
    {
        $sources = [];

        foreach ($this->sources as $name => $source) {
            $sources[] = [$name => $this->sourceToArray($source)];
        }

        $query = [
            'composite' => [
                'sources' => $sources,
                'size' => $this->size,
            ]
        ];

        if (!is_null($this->after)) {
            $query['composite']['after'] = $this->after;
        }

        return $query;
    }

    /**
     * Turns a source into its array: a string becomes a `terms` source on that field, an OpenSearchQuery
     * is built, and an array is passed through as is.
     *
     * @param string|OpenSearchQuery|array<string, mixed> $source
     * @return array<string, mixed>
     */
    private function sourceToArray(string|OpenSearchQuery|array $source): array
    {
        if (is_string($source)) {
            return ['terms' => ['field' => $source]];
        }

        if ($source instanceof OpenSearchQuery) {
            return $source->toOpenSearchQuery();
        }

        return $source;
    }
}
