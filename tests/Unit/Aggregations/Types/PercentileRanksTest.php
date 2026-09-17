<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\PercentileRanks;
use PHPUnit\Framework\TestCase;

class PercentileRanksTest extends TestCase
{
    public function testBuildsAPercentileRanksAggregation()
    {
        $this->assertEquals([
            'percentile_ranks' => [
                'field' => 'price',
                'values' => [10, 20],
            ],
        ], PercentileRanks::make('price', [10, 20])->toOpenSearchQuery());
    }
}
