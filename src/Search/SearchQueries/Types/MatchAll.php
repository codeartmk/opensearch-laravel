<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MatchAll implements SearchQueryType, OpenSearchQuery
{
    public static function make(): self
    {
        return new self();
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'match_all' => new \stdClass()
        ];
    }
}
