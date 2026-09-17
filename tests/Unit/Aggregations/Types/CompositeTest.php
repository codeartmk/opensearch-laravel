<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Composite;
use Codeart\OpensearchLaravel\Aggregations\Types\DateHistogram;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    public function testBuildsACompositeAggregationFromFieldNames()
    {
        $this->assertEquals([
            'composite' => [
                'sources' => [
                    ['category' => ['terms' => ['field' => 'category']]],
                ],
                'size' => 10,
            ],
        ], Composite::make(['category' => 'category'])->toOpenSearchQuery());
    }

    public function testBuildsACompositeAggregationFromMixedSourcesWithSizeAndAfter()
    {
        $aggregation = Composite::make(
            sources: [
                'category' => 'category',
                'month' => DateHistogram::make('created_at', 'month'),
                'tag' => ['terms' => ['field' => 'tags', 'missing_bucket' => true]],
            ],
            size: 2,
            after: ['category' => 'animals', 'month' => 1767225600000, 'tag' => 'fox']
        );

        $this->assertEquals([
            'composite' => [
                'sources' => [
                    ['category' => ['terms' => ['field' => 'category']]],
                    ['month' => ['date_histogram' => ['field' => 'created_at', 'calendar_interval' => 'month']]],
                    ['tag' => ['terms' => ['field' => 'tags', 'missing_bucket' => true]]],
                ],
                'size' => 2,
                'after' => ['category' => 'animals', 'month' => 1767225600000, 'tag' => 'fox'],
            ],
        ], $aggregation->toOpenSearchQuery());
    }
}
