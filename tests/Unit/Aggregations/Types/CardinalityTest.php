<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Cardinality;
use PHPUnit\Framework\TestCase;

class CardinalityTest extends TestCase
{
    public function testBuildsACardinalityAggregation()
    {
        $this->assertEquals([
            'cardinality' => ['field' => 'user_id'],
        ], Cardinality::make('user_id')->toOpenSearchQuery());
    }
}
