<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `top_hits` metric aggregation: the best-matching documents in each bucket, typically used as a sub-aggregation
 * of a bucket aggregation such as Terms.
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/top-hits/
 */
class TopHits implements OpenSearchQuery, AggregationType
{
    /**
     * @param int $size The number of documents to return per bucket. Always sent; 3 is also OpenSearch's default
     * @param array<array-key, mixed>|null $sort The sort, in the request body's `sort` format, e.g. [['order_date' =>
     *                                           ['order' => 'desc']]]. Null leaves it out so the documents are sorted
     *                                           by score
     * @param list<string>|array<string, mixed>|bool|null $source Fields to return, e.g. ['title', 'price'], or false to
     *                                                            leave out the source. Sent as `_source`. Null leaves
     *                                                            it out so the whole source is returned
     * @param int|null $from The offset of the first document to return. Null leaves it out so OpenSearch's default (0)
     *                       applies
     */
    public function __construct(
        private readonly int $size,
        private readonly ?array $sort,
        private readonly array|bool|null $source,
        private readonly ?int $from
    ){}

    /**
     * @param int $size The number of documents to return per bucket. Always sent; 3 is also OpenSearch's default
     * @param array<array-key, mixed>|null $sort The sort, in the request body's `sort` format, e.g. [['order_date' =>
     *                                           ['order' => 'desc']]]. Null leaves it out so the documents are sorted
     *                                           by score
     * @param list<string>|array<string, mixed>|bool|null $source Fields to return, e.g. ['title', 'price'], or false to
     *                                                            leave out the source. Sent as `_source`. Null leaves
     *                                                            it out so the whole source is returned
     * @param int|null $from The offset of the first document to return. Null leaves it out so OpenSearch's default (0)
     *                       applies
     * @return self
     */
    public static function make(int $size = 3, ?array $sort = null, array|bool|null $source = null, ?int $from = null): self
    {
        return new self($size, $sort, $source, $from);
    }

    /**
     * @return array{top_hits: array{size: int, sort?: array<array-key, mixed>, _source?: list<string>|array<string, mixed>|bool, from?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'top_hits' => [
                'size' => $this->size,
            ]
        ];

        if (!is_null($this->sort)) {
            $query['top_hits']['sort'] = $this->sort;
        }

        if (!is_null($this->source)) {
            $query['top_hits']['_source'] = $this->source;
        }

        if (!is_null($this->from)) {
            $query['top_hits']['from'] = $this->from;
        }

        return $query;
    }
}
