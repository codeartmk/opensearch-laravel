<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `reverse_nested` bucket aggregation: used inside a Nested aggregation, steps back out to the parent documents
 * so its sub-aggregations run over them.
 *
 * Without a path the body is emitted as a stdClass, so it is sent as the empty JSON object `{}`; an empty array
 * would be sent as `[]`, which OpenSearch rejects.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/reverse-nested/
 */
class ReverseNested implements OpenSearchQuery, AggregationType
{
    /**
     * @param string|null $path The nested path to step back to. Null leaves it out so it steps back to the root
     *                          document, sent as `{}`
     */
    public function __construct(
        private readonly ?string $path
    ){}

    /**
     * @param string|null $path The nested path to step back to. Null leaves it out so it steps back to the root
     *                          document, sent as `{}`
     * @return self
     */
    public static function make(?string $path = null): self
    {
        return new self($path);
    }

    /**
     * @return array{reverse_nested: array{path: string}|\stdClass} Without a path it is sent as `{"reverse_nested": {}}`
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'reverse_nested' => is_null($this->path) ? new \stdClass() : ['path' => $this->path]
        ];
    }
}
