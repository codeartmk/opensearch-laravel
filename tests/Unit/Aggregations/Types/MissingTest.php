<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Missing;
use PHPUnit\Framework\TestCase;

class MissingTest extends TestCase
{
    public function testBuildsAMissingAggregation()
    {
        $this->assertEquals([
            'missing' => ['field' => 'discount'],
        ], Missing::make('discount')->toOpenSearchQuery());
    }
}
