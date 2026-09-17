<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class CumulativeSum implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $bucketsPath,
        private readonly ?string $format
    ){}

    public static function make(string $bucketsPath, ?string $format = null): self
    {
        return new self($bucketsPath, $format);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'cumulative_sum' => [
                'buckets_path' => $this->bucketsPath,
            ]
        ];

        if (!is_null($this->format)) {
            $query['cumulative_sum']['format'] = $this->format;
        }

        return $query;
    }
}
