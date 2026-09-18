<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

/**
 * A `filters` bucket aggregation: one named bucket per query, each holding the documents that match it.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/filters/
 */
class Filters implements OpenSearchQuery, AggregationType
{
    /**
     * @param array<string, SearchQueryType|BoolQuery> $filters Bucket name => query
     * @param string|null $otherBucketKey The name of an extra bucket for the documents that match none of the queries.
     *                                    Null leaves it out, so there is no such bucket
     */
    public function __construct(
        private readonly array $filters,
        private readonly ?string $otherBucketKey
    ){}

    /**
     * @param array<string, SearchQueryType|BoolQuery> $filters Bucket name => query
     * @param string|null $otherBucketKey The name of an extra bucket for the documents that match none of the queries.
     *                                    Null leaves it out, so there is no such bucket
     * @return self
     */
    public static function make(array $filters, ?string $otherBucketKey = null): self
    {
        return new self($filters, $otherBucketKey);
    }

    /**
     * @return array{filters: array{filters: array<string, array<string, mixed>>, other_bucket_key?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'filters' => [
                'filters' => array_map(fn($filter) => $filter->toOpenSearchQuery(), $this->filters),
            ]
        ];

        if (!is_null($this->otherBucketKey)) {
            $query['filters']['other_bucket_key'] = $this->otherBucketKey;
        }

        return $query;
    }
}
