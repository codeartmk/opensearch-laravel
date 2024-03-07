<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class DateRange implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly array $ranges,
        private readonly ?string $format
    ){}

    public static function make(string $field, array $ranges, string $format = null): self
    {
        return new self($field, $ranges, $format);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'date_range' => [
                'field' => $this->field,
                'ranges' => $this->ranges
            ]
        ];

        if(!is_null($this->format)) {
            $query['date_range']['format'] = $this->format;
        }

        return $query;
    }
}