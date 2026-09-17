<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\BucketScript;
use PHPUnit\Framework\TestCase;

class BucketScriptTest extends TestCase
{
    public function testBuildsABucketScriptAggregation()
    {
        $this->assertEquals([
            'bucket_script' => [
                'buckets_path' => ['total' => 'total_sales'],
                'script' => 'params.total > 100',
            ],
        ], BucketScript::make(['total' => 'total_sales'], 'params.total > 100')->toOpenSearchQuery());
    }

    public function testBuildsABucketScriptAggregationWithAGapPolicy()
    {
        $this->assertEquals([
            'bucket_script' => [
                'buckets_path' => ['total' => 'total_sales'],
                'script' => 'params.total > 100',
                'gap_policy' => 'skip',
            ],
        ], BucketScript::make(['total' => 'total_sales'], 'params.total > 100', 'skip')->toOpenSearchQuery());
    }
}
