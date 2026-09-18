<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;

/**
 * A `nested` query: runs a query against nested objects and returns the root documents that contain a match.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/joining/nested/
 */
class Nested implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $path The path of the nested field
     * @param SearchQueryType|BoolQuery $query The query run against the nested objects; field names include the path,
     *                                         e.g. 'comments.author'
     * @param string|null $scoreMode How the scores of the matching nested objects combine into the root score: avg,
     *                               max, min, sum or none. Null leaves it out so OpenSearch's default (avg) applies
     * @param array<string, mixed>|null $innerHits Options for returning the matching nested objects with each hit; []
     *                                             returns them with the default options (sent as `{}`). Null leaves
     *                                             inner_hits out
     */
    public function __construct(
        private readonly string $path,
        private readonly SearchQueryType|BoolQuery $query,
        private readonly ?string $scoreMode,
        private readonly ?array $innerHits
    ){}

    /**
     * @param string $path The path of the nested field
     * @param SearchQueryType|BoolQuery $query The query run against the nested objects; field names include the path,
     *                                         e.g. 'comments.author'
     * @param string|null $scoreMode How the scores of the matching nested objects combine into the root score: avg,
     *                               max, min, sum or none. Null leaves it out so OpenSearch's default (avg) applies
     * @param array<string, mixed>|null $innerHits Options for returning the matching nested objects with each hit; []
     *                                             returns them with the default options (sent as `{}`). Null leaves
     *                                             inner_hits out
     * @return self
     */
    public static function make(
        string $path,
        SearchQueryType|BoolQuery $query,
        ?string $scoreMode = null,
        ?array $innerHits = null
    ): self
    {
        return new self($path, $query, $scoreMode, $innerHits);
    }

    /**
     * @return array{nested: array{path: string, query: array<string, mixed>, score_mode?: string, inner_hits?: array<string, mixed>|\stdClass}}
     */
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
