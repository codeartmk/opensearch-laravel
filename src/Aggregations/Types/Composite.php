<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Composite implements OpenSearchQuery, AggregationType
{
    /**
     * @param array<string, string|AggregationType|array> $sources Source name => field name (a terms source),
     *                                                             aggregation such as Histogram or DateHistogram, or raw source array
     */
    public function __construct(
        private readonly array $sources,
        private readonly int $size,
        private readonly ?array $after
    ){}

    /**
     * @param array<string, string|AggregationType|array> $sources Source name => field name (a terms source),
     *                                                             aggregation such as Histogram or DateHistogram, or raw source array
     */
    public static function make(array $sources, int $size = 10, ?array $after = null): self
    {
        return new self($sources, $size, $after);
    }

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
