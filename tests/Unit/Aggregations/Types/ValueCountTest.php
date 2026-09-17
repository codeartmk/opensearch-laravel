<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\ValueCount;
use PHPUnit\Framework\TestCase;

class ValueCountTest extends TestCase
{
    public function testBuildsAValueCountAggregation()
    {
        $this->assertEquals([
            'value_count' => ['field' => 'tags'],
        ], ValueCount::make('tags')->toOpenSearchQuery());
    }
}
