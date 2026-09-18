<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\BucketSort;
use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use PHPUnit\Framework\TestCase;

class BucketSortTest extends TestCase
{
    public function testBuildsABucketSortAggregation()
    {
        $this->assertEquals([
            'bucket_sort' => [
                'sort' => ['_key'],
            ],
        ], BucketSort::make('_key')->toOpenSearchQuery());
    }

    public function testBuildsABucketSortAggregationWithOrderSizeAndFrom()
    {
        $this->assertEquals([
            'bucket_sort' => [
                'sort' => [
                    ['total_sales' => ['order' => 'desc']],
                ],
                'size' => 5,
                'from' => 10,
            ],
        ], BucketSort::make('total_sales', 'desc', 5, 10)->toOpenSearchQuery());
    }

    public function testBuildsATruncateOnlyBucketSortWithSize()
    {
        $this->assertSame([
            'bucket_sort' => [
                'size' => 3,
            ],
        ], BucketSort::make(size: 3)->toOpenSearchQuery());
    }

    public function testBuildsATruncateOnlyBucketSortWithFrom()
    {
        $this->assertSame([
            'bucket_sort' => [
                'from' => 2,
            ],
        ], BucketSort::make(from: 2)->toOpenSearchQuery());
    }

    public function testBuildsATruncateOnlyBucketSortWithSizeAndFrom()
    {
        $this->assertSame([
            'bucket_sort' => [
                'size' => 3,
                'from' => 2,
            ],
        ], BucketSort::make(size: 3, from: 2)->toOpenSearchQuery());
    }

    public function testBuildsATruncateOnlyBucketSortWithSizeAndAZeroFrom()
    {
        $this->assertSame([
            'bucket_sort' => [
                'size' => 3,
                'from' => 0,
            ],
        ], BucketSort::make(size: 3, from: 0)->toOpenSearchQuery());
    }

    public function testThrowsWhenAnOrderIsGivenWithoutAField()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('BucketSort requires a field to apply an order to.');

        BucketSort::make(order: 'desc', size: 3);
    }

    public function testThrowsWhenNothingIsGiven()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('BucketSort requires a field to sort by, a size or a from above 0.');

        BucketSort::make();
    }

    public function testThrowsWhenOnlyAZeroFromIsGiven()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('BucketSort requires a field to sort by, a size or a from above 0.');

        BucketSort::make(from: 0);
    }

    public function testThrowsWhenNothingIsGivenToTheConstructor()
    {
        $this->expectException(InvalidAggregationParametersException::class);

        new BucketSort();
    }

    public function testBuildsABucketSortWithTheSmallestValidSize()
    {
        $this->assertSame([
            'bucket_sort' => [
                'sort' => ['_key'],
                'size' => 1,
            ],
        ], BucketSort::make('_key', size: 1)->toOpenSearchQuery());
    }

    public function testBuildsABucketSortWithASizeOfOneAndAZeroFrom()
    {
        $this->assertSame([
            'bucket_sort' => [
                'size' => 1,
                'from' => 0,
            ],
        ], BucketSort::make(size: 1, from: 0)->toOpenSearchQuery());
    }

    public function testThrowsWhenSizeIsZero()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('BucketSort size must be at least 1, 0 given.');

        BucketSort::make(size: 0);
    }

    public function testThrowsWhenSizeIsZeroWithAField()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('BucketSort size must be at least 1, 0 given.');

        BucketSort::make('_key', size: 0);
    }

    public function testThrowsWhenSizeIsNegative()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('BucketSort size must be at least 1, -1 given.');

        BucketSort::make('_key', size: -1);
    }

    public function testThrowsWhenFromIsNegative()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('BucketSort from must be 0 or more, -1 given.');

        BucketSort::make(from: -1);
    }

    public function testThrowsWhenFromIsNegativeWithASize()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('BucketSort from must be 0 or more, -1 given.');

        BucketSort::make('_key', size: 2, from: -1);
    }

    public function testThrowsForAnInvalidSizeGivenToTheConstructor()
    {
        $this->expectException(InvalidAggregationParametersException::class);

        new BucketSort(size: 0);
    }
}
