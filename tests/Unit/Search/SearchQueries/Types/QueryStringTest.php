<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\QueryString;
use PHPUnit\Framework\TestCase;

class QueryStringTest extends TestCase
{
    public function testBuildsAQueryStringQuery()
    {
        $this->assertEquals([
            'query_string' => [
                'query' => 'fox bread',
            ],
        ], QueryString::make('fox bread')->toOpenSearchQuery());
    }

    public function testBuildsAQueryStringQueryWithFieldsAndDefaultOperator()
    {
        $this->assertEquals([
            'query_string' => [
                'query' => 'quick brown',
                'fields' => ['title', 'body'],
                'default_operator' => 'AND',
            ],
        ], QueryString::make('quick brown', ['title', 'body'], 'AND')->toOpenSearchQuery());
    }
}
