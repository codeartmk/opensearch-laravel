<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class GeohashGrid implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly ?int $precision,
        private readonly ?int $size
    ){}

    public static function make(string $field, ?int $precision = null, ?int $size = null): self
    {
        return new self($field, $precision, $size);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'geohash_grid' => [
                'field' => $this->field,
            ]
        ];

        if (!is_null($this->precision)) {
            $query['geohash_grid']['precision'] = $this->precision;
        }

        if (!is_null($this->size)) {
            $query['geohash_grid']['size'] = $this->size;
        }

        return $query;
    }
}
