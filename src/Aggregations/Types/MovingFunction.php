<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `moving_fn` pipeline aggregation: runs a script over a sliding window of the buckets of a parent histogram or
 * date histogram, e.g. for moving averages.
 *
 * @see https://opensearch.org/docs/latest/aggregations/pipeline/moving-function/
 */
class MovingFunction implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $bucketsPath The path to the metric, relative to the parent aggregation, e.g. 'sales'
     * @param int $window The number of buckets in the window
     * @param string $script The script run on each window, with the window's values as `values`, e.g.
     *                       'MovingFunctions.unweightedAvg(values)'
     * @param int|null $shift Moves the window; a positive shift includes the current bucket and later ones. Null leaves
     *                        it out so OpenSearch's default (0, the window ends before the current bucket) applies
     */
    public function __construct(
        private readonly string $bucketsPath,
        private readonly int $window,
        private readonly string $script,
        private readonly ?int $shift
    ){}

    /**
     * @param string $bucketsPath The path to the metric, relative to the parent aggregation, e.g. 'sales'
     * @param int $window The number of buckets in the window
     * @param string $script The script run on each window, with the window's values as `values`, e.g.
     *                       'MovingFunctions.unweightedAvg(values)'
     * @param int|null $shift Moves the window; a positive shift includes the current bucket and later ones. Null leaves
     *                        it out so OpenSearch's default (0, the window ends before the current bucket) applies
     * @return self
     */
    public static function make(string $bucketsPath, int $window, string $script, ?int $shift = null): self
    {
        return new self($bucketsPath, $window, $script, $shift);
    }

    /**
     * @return array{moving_fn: array{buckets_path: string, window: int, script: string, shift?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'moving_fn' => [
                'buckets_path' => $this->bucketsPath,
                'window' => $this->window,
                'script' => $this->script,
            ]
        ];

        if (!is_null($this->shift)) {
            $query['moving_fn']['shift'] = $this->shift;
        }

        return $query;
    }
}
