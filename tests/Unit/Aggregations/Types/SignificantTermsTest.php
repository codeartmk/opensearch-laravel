<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\SignificantTerms;
use PHPUnit\Framework\TestCase;

class SignificantTermsTest extends TestCase
{
    public function testBuildsASignificantTermsAggregation()
    {
        $this->assertEquals([
            'significant_terms' => ['field' => 'tags'],
        ], SignificantTerms::make('tags')->toOpenSearchQuery());
    }

    public function testBuildsASignificantTermsAggregationWithSize()
    {
        $this->assertEquals([
            'significant_terms' => ['field' => 'tags', 'size' => 3],
        ], SignificantTerms::make('tags', 3)->toOpenSearchQuery());
    }
}
