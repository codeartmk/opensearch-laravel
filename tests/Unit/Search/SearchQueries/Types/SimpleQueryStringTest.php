<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SimpleQueryString;
use PHPUnit\Framework\TestCase;

class SimpleQueryStringTest extends TestCase
{
    public function testBuildsASimpleQueryStringQuery()
    {
        $this->assertEquals([
            'simple_query_string' => [
                'query' => 'fox bread',
            ],
        ], SimpleQueryString::make('fox bread')->toOpenSearchQuery());
    }

    public function testBuildsASimpleQueryStringQueryWithFieldsAndDefaultOperator()
    {
        $this->assertEquals([
            'simple_query_string' => [
                'query' => 'quick brown',
                'fields' => ['title', 'body'],
                'default_operator' => 'AND',
            ],
        ], SimpleQueryString::make('quick brown', ['title', 'body'], 'AND')->toOpenSearchQuery());
    }
}
