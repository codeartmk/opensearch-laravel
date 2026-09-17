<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Percentile implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly ?array $percents = null,
    ){}

    public static function make(string $field, ?array $percents = null): self
    {
        return new self($field, $percents);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'percentiles' => [
                'field' => $this->field,
            ]
        ];

        if (!is_null($this->percents)) {
            $query['percentiles']['percents'] = $this->percents;
        }

        return $query;
    }
}
