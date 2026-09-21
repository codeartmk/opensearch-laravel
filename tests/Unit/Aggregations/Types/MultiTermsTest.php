<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\MultiTerms;
use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use PHPUnit\Framework\TestCase;

class MultiTermsTest extends TestCase
{
    public function testBuildsAMultiTermsAggregation()
    {
        $this->assertEquals([
            'multi_terms' => [
                'terms' => [
                    ['field' => 'category'],
                    ['field' => 'tags'],
                ],
                'size' => 5,
            ],
        ], MultiTerms::make(['category', 'tags'], 5)->toOpenSearchQuery());
    }

    public function testDropsTheKeysOfTheFields()
    {
        $this->assertEquals([
            'multi_terms' => [
                'terms' => [
                    ['field' => 'category'],
                    ['field' => 'tags'],
                ],
                'size' => 10,
            ],
        ], MultiTerms::make(['first' => 'category', 'second' => 'tags'])->toOpenSearchQuery());
    }

    public function testThrowsForAnEmptyList()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('MultiTerms requires at least two fields, 0 given; use Terms for a single field.');

        MultiTerms::make([]);
    }

    public function testThrowsForASingleField()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('MultiTerms requires at least two fields, 1 given; use Terms for a single field.');

        MultiTerms::make(['category']);
    }

    public function testThrowsForAFieldThatIsNotAString()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage('MultiTerms accepts only field names (strings) as fields, array given at key 1.');

        MultiTerms::make(['category', ['field' => 'tags']]);
    }

    public function testThrowsForAFieldThatIsNull()
    {
        $this->expectException(InvalidAggregationParametersException::class);
        $this->expectExceptionMessage("MultiTerms accepts only field names (strings) as fields, null given at key 'tags'.");

        MultiTerms::make(['category' => 'category', 'tags' => null]);
    }
}
