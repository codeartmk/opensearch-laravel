<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class ScriptedMetric implements OpenSearchQuery, AggregationType
{
    public function __construct(
        private readonly string $mapScript,
        private readonly string $combineScript,
        private readonly string $reduceScript,
        private readonly ?string $initScript,
        private readonly ?array $params
    ){}

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
