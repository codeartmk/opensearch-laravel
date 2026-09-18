<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `global` bucket aggregation: a single bucket holding every document in the index, ignoring the search query.
 * Only valid at the top level. Named GlobalBucket because `global` is a reserved word in PHP.
 *
 * It has no options, but its body must be the empty JSON object `{}`, so it is emitted as a stdClass; an empty
 * array would be sent as `[]`, which OpenSearch rejects.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/global/
 */
class GlobalBucket implements OpenSearchQuery, AggregationType
{
    public static function make(): self
    {
        return new self();
    }

    /**
     * @return array{global: \stdClass} Sent as `{"global": {}}`
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'global' => new \stdClass()
        ];
    }
}
