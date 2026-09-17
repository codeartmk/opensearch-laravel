<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

class Filters implements OpenSearchQuery, AggregationType
{
    /**
     * @param array<string, SearchQueryType|BoolQuery> $filters Bucket name => query
     */
    public function __construct(
        private readonly array $filters,
        private readonly ?string $otherBucketKey
    ){}

    /**
     * @param array<string, SearchQueryType|BoolQuery> $filters Bucket name => query
     */
    public static function make(array $filters, ?string $otherBucketKey = null): self
    {
        return new self($filters, $otherBucketKey);
    }

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
