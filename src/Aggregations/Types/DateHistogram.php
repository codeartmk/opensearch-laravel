<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `date_histogram` bucket aggregation: buckets documents by a date field into intervals, either calendar-aware (a
 * month, a year) or fixed (a number of units).
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/date-histogram/
 */
class DateHistogram implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The date field
     * @param string $interval The bucket interval: a calendar unit such as 'month' or '1M', or with $isIntervalFixed a
     *                         fixed amount such as '30d'
     * @param bool $isIntervalFixed False sends the interval as `calendar_interval`, true as `fixed_interval`
     * @param string|null $format The date format of the bucket keys, e.g. 'yyyy-MM-dd'. Null leaves it out so the
     *                            field's format applies
     * @param string|null $offset Shifts the bucket boundaries, e.g. '+6h'. Null leaves it out so there is no offset
     */
    public function __construct(
        private readonly string $field,
        private readonly string $interval,
        private readonly bool $isIntervalFixed,
        private readonly ?string $format,
        private readonly ?string $offset
    ){}

    /**
     * @param string $field The date field
     * @param string $interval The bucket interval: a calendar unit such as 'month' or '1M', or with $isIntervalFixed a
     *                         fixed amount such as '30d'
     * @param bool $isIntervalFixed False sends the interval as `calendar_interval`, true as `fixed_interval`
     * @param string|null $format The date format of the bucket keys, e.g. 'yyyy-MM-dd'. Null leaves it out so the
     *                            field's format applies
     * @param string|null $offset Shifts the bucket boundaries, e.g. '+6h'. Null leaves it out so there is no offset
     * @return self
     */
    public static function make(
        string $field,
        string $interval,
        bool $isIntervalFixed = false,
        ?string $format = null,
        ?string $offset = null
    ): self
    {
        return new self($field, $interval, $isIntervalFixed, $format, $offset);
    }

    /**
     * @return array{date_histogram: array{field: string, calendar_interval?: string, fixed_interval?: string, offset?: string, format?: string}}
     */
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
            $query['date_histogram']['format'] = $this->format;
        }

        return $query;
    }
}