<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Percentiles;
use PHPUnit\Framework\TestCase;

class PercentilesTest extends TestCase
{
    public function testBuildsAPercentilesAggregation()
    {
        $this->assertEquals([
            'percentiles' => ['field' => 'price'],
        ], Percentiles::make('price')->toOpenSearchQuery());
    }

    public function testBuildsAPercentilesAggregationWithPercents()
    {
        $this->assertEquals([
            'percentiles' => [
                'field' => 'price',
                'percents' => [50, 95, 99],
            ],
        ], Percentiles::make('price', [50, 95, 99])->toOpenSearchQuery());
    }
}
