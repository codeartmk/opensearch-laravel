<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `cumulative_sum` pipeline aggregation: the running total of a metric across the buckets of a parent histogram
 * or date histogram.
 *
 * @see https://opensearch.org/docs/latest/aggregations/pipeline/cumulative-sum/
 */
class CumulativeSum implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $bucketsPath The path to the metric, relative to the parent aggregation, e.g. 'sales'
     * @param string|null $format The DecimalFormat pattern of the output's `value_as_string`, e.g. '#,##0.00'. Null
     *                            leaves it out so no formatted value is returned
     */
    public function __construct(
        private readonly string $bucketsPath,
        private readonly ?string $format
    ){}

    /**
     * @param string $bucketsPath The path to the metric, relative to the parent aggregation, e.g. 'sales'
     * @param string|null $format The DecimalFormat pattern of the output's `value_as_string`, e.g. '#,##0.00'. Null
     *                            leaves it out so no formatted value is returned
     * @return self
     */
    public static function make(string $bucketsPath, ?string $format = null): self
    {
        return new self($bucketsPath, $format);
    }

    /**
     * @return array{cumulative_sum: array{buckets_path: string, format?: string}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'cumulative_sum' => [
                'buckets_path' => $this->bucketsPath,
            ]
        ];

        if (!is_null($this->format)) {
            $query['cumulative_sum']['format'] = $this->format;
        }

        return $query;
    }
}
