<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Min;
use PHPUnit\Framework\TestCase;

class MinTest extends TestCase
{
    public function testBuildsAMinAggregation()
    {
        $this->assertEquals([
            'min' => ['field' => 'price'],
        ], Min::make('price')->toOpenSearchQuery());
    }
}
