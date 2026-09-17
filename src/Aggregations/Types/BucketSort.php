<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class BucketSort implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly ?string $order = null,
        private readonly ?int $size = null,
        private readonly ?int $from = null,
    ){}

    public static function make(string $field, ?string $order = null, ?int $size = null, ?int $from = null): self
    {
        return new self($field, $order, $size, $from);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'bucket_sort' => [
                'sort' => [
                    is_null($this->order) ? $this->field : [$this->field => ['order' => $this->order]],
                ],
            ]
        ];

        if (!is_null($this->size)) {
            $query['bucket_sort']['size'] = $this->size;
        }

        if (!is_null($this->from)) {
            $query['bucket_sort']['from'] = $this->from;
        }

        return $query;
    }
}
