<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MaxBucket implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $bucketsPath,
        private readonly ?string $gapPolicy
    ){}

    public static function make(string $bucketsPath, ?string $gapPolicy = null): self
    {
        return new self($bucketsPath, $gapPolicy);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'max_bucket' => [
                'buckets_path' => $this->bucketsPath,
            ]
        ];

        if (!is_null($this->gapPolicy)) {
            $query['max_bucket']['gap_policy'] = $this->gapPolicy;
        }

        return $query;
    }
}
