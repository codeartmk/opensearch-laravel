<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Terms;
use PHPUnit\Framework\TestCase;

class TermsTest extends TestCase
{
    public function testBuildsATermsAggregation()
    {
        $this->assertEquals([
            'terms' => [
                'field' => 'category',
                'size' => 10,
            ],
        ], Terms::make('category')->toOpenSearchQuery());
    }

    public function testBuildsATermsAggregationWithOrderAndMinimumDocumentCount()
    {
        $this->assertEquals([
            'terms' => [
                'field' => 'category',
                'size' => 20,
                'order' => ['_count' => 'asc'],
                'min_doc_count' => 5,
            ],
        ], Terms::make('category', 20, ['_count' => 'asc'], 5)->toOpenSearchQuery());
    }
}
