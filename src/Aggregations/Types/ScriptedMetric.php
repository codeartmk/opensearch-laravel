<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `scripted_metric` metric aggregation: a metric calculated by your own scripts, run per shard (init, map,
 * combine) and then once to merge the shard results (reduce).
 *
 * @see https://opensearch.org/docs/latest/aggregations/metric/scripted-metric/
 */
class ScriptedMetric implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $mapScript Runs once per document, e.g. "state.total += doc['price'].value"
     * @param string $combineScript Runs once per shard and returns that shard's result
     * @param string $reduceScript Runs once and merges the shard results, available as `states`
     * @param string|null $initScript Runs once per shard before any document, e.g. to set up `state`. Null leaves it
     *                                out
     * @param array<string, mixed>|null $params Values made available to the scripts as `params`. Null or an empty array
     *                                          leaves it out
     */
    public function __construct(
        private readonly string $mapScript,
        private readonly string $combineScript,
        private readonly string $reduceScript,
        private readonly ?string $initScript,
        private readonly ?array $params
    ){}

    /**
     * @param string $mapScript Runs once per document, e.g. "state.total += doc['price'].value"
     * @param string $combineScript Runs once per shard and returns that shard's result
     * @param string $reduceScript Runs once and merges the shard results, available as `states`
     * @param string|null $initScript Runs once per shard before any document, e.g. to set up `state`. Null leaves it
     *                                out
     * @param array<string, mixed>|null $params Values made available to the scripts as `params`. Null or an empty array
     *                                          leaves it out
     * @return self
     */
    public static function make(
        string $mapScript,
        string $combineScript,
        string $reduceScript,
        ?string $initScript = null,
        ?array $params = null
    ): self
    {
        return new self($mapScript, $combineScript, $reduceScript, $initScript, $params);
    }

    /**
     * @return array{scripted_metric: array{init_script?: string, map_script: string, combine_script: string, reduce_script: string, params?: array<string, mixed>}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'scripted_metric' => []
        ];

        if (!is_null($this->initScript)) {
            $query['scripted_metric']['init_script'] = $this->initScript;
        }

        $query['scripted_metric']['map_script'] = $this->mapScript;
        $query['scripted_metric']['combine_script'] = $this->combineScript;
        $query['scripted_metric']['reduce_script'] = $this->reduceScript;

        if (!empty($this->params)) {
            $query['scripted_metric']['params'] = $this->params;
        }

        return $query;
    }
}
