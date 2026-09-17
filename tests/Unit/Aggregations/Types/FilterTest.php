<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Filter;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testBuildsAFilterAggregation()
    {
        $this->assertEquals([
            'filter' => ['term' => ['category' => 'food']],
        ], Filter::make(Term::make('category', 'food'))->toOpenSearchQuery());
    }
}
