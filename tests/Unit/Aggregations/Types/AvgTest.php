<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Avg;
use PHPUnit\Framework\TestCase;

class AvgTest extends TestCase
{
    public function testBuildsAAvgAggregation()
    {
        $this->assertEquals([
            'avg' => ['field' => 'age'],
        ], Avg::make('age')->toOpenSearchQuery());
    }
}
