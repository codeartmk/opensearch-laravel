<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * The `global` aggregation. Named GlobalBucket because `global` is a reserved word in PHP.
 */
class GlobalBucket implements OpenSearchQuery, AggregationType
{
    public static function make(): self
    {
        return new self();
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'global' => new \stdClass()
        ];
    }
}
