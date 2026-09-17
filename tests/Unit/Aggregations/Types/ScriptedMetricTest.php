<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\ScriptedMetric;
use PHPUnit\Framework\TestCase;

class ScriptedMetricTest extends TestCase
{
    public function testBuildsAScriptedMetricAggregation()
    {
        $aggregation = ScriptedMetric::make(
            mapScript: 'state.total += 1',
            combineScript: 'return state.total',
            reduceScript: 'double sum = 0; for (t in states) { sum += t } return sum'
        );

        $this->assertEquals([
            'scripted_metric' => [
                'map_script' => 'state.total += 1',
                'combine_script' => 'return state.total',
                'reduce_script' => 'double sum = 0; for (t in states) { sum += t } return sum',
            ],
        ], $aggregation->toOpenSearchQuery());
    }

    public function testBuildsAScriptedMetricAggregationWithAnInitScriptAndParams()
    {
        $aggregation = ScriptedMetric::make(
            'state.total += params.step',
            'return state.total',
            'double sum = 0; for (t in states) { sum += t } return sum',
            'state.total = 0',
            ['step' => 2]
        );

        $this->assertSame([
            'scripted_metric' => [
                'init_script' => 'state.total = 0',
                'map_script' => 'state.total += params.step',
                'combine_script' => 'return state.total',
                'reduce_script' => 'double sum = 0; for (t in states) { sum += t } return sum',
                'params' => ['step' => 2],
            ],
        ], $aggregation->toOpenSearchQuery());
    }
}
