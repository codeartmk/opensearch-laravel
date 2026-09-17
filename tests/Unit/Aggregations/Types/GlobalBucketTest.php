<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\GlobalBucket;
use PHPUnit\Framework\TestCase;

class GlobalBucketTest extends TestCase
{
    public function testBuildsAGlobalAggregation()
    {
        $this->assertSame('{"global":{}}', json_encode(GlobalBucket::make()->toOpenSearchQuery()));
    }
}
