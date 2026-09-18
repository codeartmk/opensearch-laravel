<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `date_range` bucket aggregation: one bucket per date range. Ranges include `from` and exclude `to`, and accept
 * date math such as 'now-10M/M'.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/date-range/
 */
class DateRange implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The date field
     * @param array<int, array<string, mixed>> $ranges The ranges, each with `from` and/or `to` and an optional `key`,
     *                                                 e.g. [['to' => 'now-10M/M'], ['from' => 'now-10M/M']]
     * @param string|null $format The date format of `from`, `to` and the bucket keys, e.g. 'MM-yyyy'. Null leaves it
     *                            out so the field's format applies
     */
    public function __construct(
        private readonly string $field,
        private readonly array $ranges,
        private readonly ?string $format
    ){}

    /**
     * @param string $field The date field
     * @param array<int, array<string, mixed>> $ranges The ranges, each with `from` and/or `to` and an optional `key`,
     *                                                 e.g. [['to' => 'now-10M/M'], ['from' => 'now-10M/M']]
     * @param string|null $format The date format of `from`, `to` and the bucket keys, e.g. 'MM-yyyy'. Null leaves it
     *                            out so the field's format applies
     * @return self
     */
    public static function make(string $field, array $ranges, ?string $format = null): self
    {
        return new self($field, $ranges, $format);
    }

    /**
     * @return array{date_range: array{field: string, ranges: array<int, array<string, mixed>>, format?: string}}
     */
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