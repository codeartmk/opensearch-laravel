<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Max;
use PHPUnit\Framework\TestCase;

class MaxTest extends TestCase
{
    public function testBuildsAMaxAggregation()
    {
        $this->assertEquals([
            'max' => ['field' => 'price'],
        ], Max::make('price')->toOpenSearchQuery());
    }
}
