<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Derivative implements OpenSearchQuery, AggregationType
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
            'derivative' => [
                'buckets_path' => $this->bucketsPath,
            ]
        ];

        if (!is_null($this->gapPolicy)) {
            $query['derivative']['gap_policy'] = $this->gapPolicy;
        }

        return $query;
    }
}
