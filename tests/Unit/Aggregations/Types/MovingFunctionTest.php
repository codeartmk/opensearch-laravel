<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\MovingFunction;
use PHPUnit\Framework\TestCase;

class MovingFunctionTest extends TestCase
{
    public function testBuildsAMovingFunctionAggregation()
    {
        $this->assertEquals([
            'moving_fn' => [
                'buckets_path' => 'sales',
                'window' => 3,
                'script' => 'MovingFunctions.unweightedAvg(values)',
            ],
        ], MovingFunction::make('sales', 3, 'MovingFunctions.unweightedAvg(values)')->toOpenSearchQuery());
    }

    public function testBuildsAMovingFunctionAggregationWithAShift()
    {
        $this->assertEquals([
            'moving_fn' => [
                'buckets_path' => 'sales',
                'window' => 3,
                'script' => 'MovingFunctions.max(values)',
                'shift' => 1,
            ],
        ], MovingFunction::make('sales', 3, 'MovingFunctions.max(values)', 1)->toOpenSearchQuery());
    }
}
