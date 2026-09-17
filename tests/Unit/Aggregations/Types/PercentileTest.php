<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Percentile;
use PHPUnit\Framework\TestCase;

class PercentileTest extends TestCase
{
    public function testBuildsAPercentilesAggregation()
    {
        $this->assertEquals([
            'percentiles' => ['field' => 'price'],
        ], Percentile::make('price')->toOpenSearchQuery());
    }

    public function testBuildsAPercentilesAggregationWithPercents()
    {
        $this->assertEquals([
            'percentiles' => [
                'field' => 'price',
                'percents' => [50, 95, 99],
            ],
        ], Percentile::make('price', [50, 95, 99])->toOpenSearchQuery());
    }
}
