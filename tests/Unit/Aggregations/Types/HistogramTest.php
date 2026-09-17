<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Histogram;
use PHPUnit\Framework\TestCase;

class HistogramTest extends TestCase
{
    public function testBuildsAHistogramAggregation()
    {
        $this->assertEquals([
            'histogram' => ['field' => 'price', 'interval' => 10],
        ], Histogram::make('price', 10)->toOpenSearchQuery());
    }

    public function testBuildsAHistogramAggregationWithAMinimumDocumentCount()
    {
        $this->assertEquals([
            'histogram' => ['field' => 'price', 'interval' => 2.5, 'min_doc_count' => 1],
        ], Histogram::make('price', 2.5, 1)->toOpenSearchQuery());
    }
}
