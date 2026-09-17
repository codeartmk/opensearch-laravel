<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Filters;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
{
    public function testBuildsANamedFiltersAggregation()
    {
        $aggregation = Filters::make([
            'food' => Term::make('category', 'food'),
            'animals' => Term::make('category', 'animals'),
        ]);

        $this->assertEquals([
            'filters' => [
                'filters' => [
                    'food' => ['term' => ['category' => 'food']],
                    'animals' => ['term' => ['category' => 'animals']],
                ],
            ],
        ], $aggregation->toOpenSearchQuery());
    }

    public function testBuildsAFiltersAggregationWithAnOtherBucket()
    {
        $aggregation = Filters::make(['food' => Term::make('category', 'food')], 'everything_else');

        $this->assertEquals([
            'filters' => [
                'filters' => [
                    'food' => ['term' => ['category' => 'food']],
                ],
                'other_bucket_key' => 'everything_else',
            ],
        ], $aggregation->toOpenSearchQuery());
    }
}
