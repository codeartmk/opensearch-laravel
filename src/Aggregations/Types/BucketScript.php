<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class BucketScript implements OpenSearchQuery, AggregationType
{
    /**
     * @param array<string, string> $bucketsPath Script variable name => buckets path
     */
    public function __construct(
        private readonly array $bucketsPath,
        private readonly string $script,
        private readonly ?string $gapPolicy
    ){}

    /**
     * @param array<string, string> $bucketsPath Script variable name => buckets path
     */
    public static function make(array $bucketsPath, string $script, ?string $gapPolicy = null): self
    {
        return new self($bucketsPath, $script, $gapPolicy);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'bucket_script' => [
                'buckets_path' => $this->bucketsPath,
                'script' => $this->script,
            ]
        ];

        if (!is_null($this->gapPolicy)) {
            $query['bucket_script']['gap_policy'] = $this->gapPolicy;
        }

        return $query;
    }
}
