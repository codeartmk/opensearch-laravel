<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Sum;
use PHPUnit\Framework\TestCase;

class SumTest extends TestCase
{
    public function testBuildsASumAggregation()
    {
        $this->assertEquals([
            'sum' => ['field' => 'price'],
        ], Sum::make('price')->toOpenSearchQuery());
    }
}
