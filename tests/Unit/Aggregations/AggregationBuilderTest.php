<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations;

use Codeart\OpensearchLaravel\Aggregations\Aggregation;
use Codeart\OpensearchLaravel\Aggregations\AggregationBuilder;
use Codeart\OpensearchLaravel\Aggregations\Types\Avg;
use Codeart\OpensearchLaravel\Aggregations\Types\Max;
use Codeart\OpensearchLaravel\Aggregations\Types\Terms;
use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Exceptions\OpenSearchException;
use PHPUnit\Framework\TestCase;

class AggregationBuilderTest extends TestCase
{
    public function testBuildsASingleAggregation()
    {
        $builder = new AggregationBuilder(Aggregation::make('average_price', Avg::make('price')));

        $this->assertEquals([
            'aggs' => [
                'average_price' => ['avg' => ['field' => 'price']],
            ],
        ], $builder->toOpenSearchQuery());
    }

    public function testBuildsAListOfAggregations()
    {
        $builder = new AggregationBuilder([
            Aggregation::make('average_price', Avg::make('price')),
            Aggregation::make('max_price', Max::make('price')),
        ]);

        $this->assertEquals([
            'aggs' => [
                'average_price' => ['avg' => ['field' => 'price']],
                'max_price' => ['max' => ['field' => 'price']],
            ],
        ], $builder->toOpenSearchQuery());
    }

    public function testThrowsOnAnEmptyList()
    {
        $this->expectException(InvalidAggregationParametersException::class);

        new AggregationBuilder([]);
    }

    public function testThrowsWhenAnItemIsNotAnAggregation()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('Aggregations must be Aggregation instances, ' . Terms::class . ' given.');

        new AggregationBuilder([Terms::make('category')]);
    }

    public function testThrowsWhenTwoAggregationsShareAName()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('Aggregation names must be unique, "price" is used more than once.');

        new AggregationBuilder([
            Aggregation::make('price', Avg::make('price')),
            Aggregation::make('price', Max::make('price')),
        ]);
    }

    public function testValidatesSubAggregationsWhenTheBuilderIsCreated()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('Aggregation names must be unique, "price" is used more than once.');

        new AggregationBuilder(Aggregation::make('categories', Terms::make('category'), [
            Aggregation::make('price', Avg::make('price')),
            Aggregation::make('price', Max::make('price')),
        ]));
    }

    public function testTheExceptionCanBeCaughtAsAPackageAndAnInvalidArgumentException()
    {
        $exception = new InvalidAggregationParametersException();

        $this->assertInstanceOf(OpenSearchException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }
}
