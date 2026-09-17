<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MovingFunction implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $bucketsPath,
        private readonly int $window,
        private readonly string $script,
        private readonly ?int $shift
    ){}

    public static function make(string $bucketsPath, int $window, string $script, ?int $shift = null): self
    {
        return new self($bucketsPath, $window, $script, $shift);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'moving_fn' => [
                'buckets_path' => $this->bucketsPath,
                'window' => $this->window,
                'script' => $this->script,
            ]
        ];

        if (!is_null($this->shift)) {
            $query['moving_fn']['shift'] = $this->shift;
        }

        return $query;
    }
}
