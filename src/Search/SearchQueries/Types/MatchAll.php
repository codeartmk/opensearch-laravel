<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `match_all` query: matches every document, each with a score of 1.0.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/match-all/
 */
class MatchAll implements SearchQueryType, OpenSearchQuery
{
    public static function make(): self
    {
        return new self();
    }

    /**
     * @return array{match_all: \stdClass} Sent as `{"match_all": {}}`
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'match_all' => new \stdClass()
        ];
    }
}
