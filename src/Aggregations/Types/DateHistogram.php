<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class DateHistogram implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $field,
        private readonly string $interval,
        private readonly bool $isIntervalFixed,
        private readonly ?string $format,
        private readonly ?string $offset
    ){}

    public static function make(
        string $field,
        string $interval,
        bool $isIntervalFixed = false,
        string $format = null,
        string $offset = null
    ): self
    {
        return new self($field, $interval, $isIntervalFixed, $format, $offset);
    }

    public function toOpenSearchQuery(): array
    {
        $query = [
            'date_histogram' => [
                'field' => $this->field,
            ]
        ];

        if ($this->isIntervalFixed) {
            $query['date_histogram']['fixed_interval'] = $this->interval;
        } else {
            $query['date_histogram']['calendar_interval'] = $this->interval;
        }

        if (!is_null($this->offset)) {
            $query['date_histogram']['offset'] = $this->offset;
        }

        if(!is_null($this->format)) {
            $query['date_range']['format'] = $this->format;
        }

        return $query;
    }
}