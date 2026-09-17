<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

class Nested implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $path,
        private readonly SearchQueryType|BoolQuery $query,
        private readonly ?string $scoreMode,
        private readonly ?array $innerHits
    ){}

    public static function make(
        string $path,
        SearchQueryType|BoolQuery $query,
        ?string $scoreMode = null,
        ?array $innerHits = null
    ): self
    {
        return new self($path, $query, $scoreMode, $innerHits);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'nested' => [
                'path' => $this->path,
                'query' => $this->query->toOpenSearchQuery()
            ]
        ];

        if (!is_null($this->scoreMode)) {
            $query['nested']['score_mode'] = $this->scoreMode;
        }

        // An empty inner_hits has to be sent as a JSON object, not an empty array.
        if (!is_null($this->innerHits)) {
            $query['nested']['inner_hits'] = $this->innerHits ?: new \stdClass();
        }

        return $query;
    }
}
