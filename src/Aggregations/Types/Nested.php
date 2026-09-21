<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `nested` bucket aggregation: steps into the nested objects at a path, so its sub-aggregations run over those
 * objects. Not to be confused with the nested query.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/nested/
 */
class Nested implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $path The path of the nested field
     */
    public function __construct(
        private readonly string $path,
    ){}

    /**
     * @param string $path The path of the nested field
     * @return self
     */
    public static function make(string $path): self
    {
        return new self($path);
    }

    /**
     * @return array{nested: array{path: string}}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'nested' => [
                'path' => $this->path,
            ]
        ];
    }
}
