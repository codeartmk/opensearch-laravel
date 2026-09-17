<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class ExtendedStats implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly int|float|null $sigma
    ){}

    public static function make(string $field, int|float|null $sigma = null): self
    {
        return new self($field, $sigma);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'extended_stats' => [
                'field' => $this->field,
            ]
        ];

        if (!is_null($this->sigma)) {
            $query['extended_stats']['sigma'] = $this->sigma;
        }

        return $query;
    }
}
