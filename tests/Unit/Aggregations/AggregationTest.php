<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations;

use Codeart\OpensearchLaravel\Aggregations\Aggregation;
use Codeart\OpensearchLaravel\Aggregations\Types\Average;
use Codeart\OpensearchLaravel\Aggregations\Types\Maximum;
use Codeart\OpensearchLaravel\Aggregations\Types\Terms;
use PHPUnit\Framework\TestCase;

class AggregationTest extends TestCase
{
    public function testBuildsAnAggregationWithoutSubAggregations()
    {
        $aggregation = Aggregation::make('average_price', Average::make('price'));

        $this->assertEquals([
            'average_price' => [
                'avg' => ['field' => 'price'],
            ],
        ], $aggregation->toOpenSearchQuery());
    }

    public function testBuildsAnAggregationWithASingleSubAggregation()
    {
        $aggregation = Aggregation::make(
            name: 'categories',
            aggregationType: Terms::make('category'),
            aggregation: Aggregation::make('average_price', Average::make('price'))
        );

        $this->assertEquals([
            'categories' => [
                'terms' => ['field' => 'category', 'size' => 10],
                'aggs' => [
                    'average_price' => [
                        'avg' => ['field' => 'price'],
                    ],
                ],
            ],
        ], $aggregation->toOpenSearchQuery());
    }

    public function testBuildsAnAggregationWithMultipleSubAggregations()
    {
        $aggregation = Aggregation::make(
            name: 'categories',
            aggregationType: Terms::make('category'),
            aggregation: [
                Aggregation::make('average_price', Average::make('price')),
                Aggregation::make('max_price', Maximum::make('price')),
            ]
        );

        $this->assertEquals([
            'categories' => [
                'terms' => ['field' => 'category', 'size' => 10],
                'aggs' => [
                    'average_price' => [
                        'avg' => ['field' => 'price'],
                    ],
                    'max_price' => [
                        'max' => ['field' => 'price'],
                    ],
                ],
            ],
        ], $aggregation->toOpenSearchQuery());
    }

    public function testIgnoresAnEmptySubAggregationList()
    {
        $aggregation = Aggregation::make('average_price', Average::make('price'), []);

        $this->assertEquals([
            'average_price' => [
                'avg' => ['field' => 'price'],
            ],
        ], $aggregation->toOpenSearchQuery());
    }
}
