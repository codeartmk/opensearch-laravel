<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class TopHits implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly int $size,
        private readonly ?array $sort,
        private readonly array|bool|null $source,
        private readonly ?int $from
    ){}

    /**
     * @param array|bool|null $source Fields to return, e.g. ['title', 'price'], or false to leave out the source
     */
    public static function make(int $size = 3, ?array $sort = null, array|bool|null $source = null, ?int $from = null): self
    {
        return new self($size, $sort, $source, $from);
    }

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
