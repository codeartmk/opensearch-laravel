<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\WeightedAvg;
use PHPUnit\Framework\TestCase;

class WeightedAvgTest extends TestCase
{
    public function testBuildsAWeightedAvgAggregation()
    {
        $this->assertEquals([
            'weighted_avg' => [
                'value' => ['field' => 'price'],
                'weight' => ['field' => 'quantity'],
            ],
        ], WeightedAvg::make('price', 'quantity')->toOpenSearchQuery());
    }
}
