<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Filters;
use Codeart\OpensearchLaravel\Aggregations\Types\Sum;
use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Must;
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

    public function testAcceptsABoolQuery()
    {
        $aggregation = Filters::make(['food' => BoolQuery::make([Must::make(Term::make('category', 'food'))])]);

        $this->assertEquals([
            'filters' => [
                'filters' => [
                    'food' => ['bool' => ['must' => ['term' => ['category' => 'food']]]],
                ],
            ],
        ], $aggregation->toOpenSearchQuery());
    }

    public function testThrowsForAnEmptyList()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('Filters requires at least one filter.');

        Filters::make([]);
    }

    public function testThrowsForAFilterThatIsNotAQuery()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage("Filters accepts only SearchQueryType or BoolQuery filters, " . Sum::class . " given at key 'animals'.");

        Filters::make(['food' => Term::make('category', 'food'), 'animals' => Sum::make('price')]);
    }
}
