<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MultiMatch;
use PHPUnit\Framework\TestCase;

class MultiMatchTest extends TestCase
{
    public function testBuildsAMultiMatchQuery()
    {
        $this->assertEquals([
            'multi_match' => [
                'query' => 'bread',
                'fields' => ['title', 'body'],
            ],
        ], MultiMatch::make('bread', ['title', 'body'])->toOpenSearchQuery());
    }

    public function testBuildsAMultiMatchQueryWithTypeAndOperator()
    {
        $this->assertEquals([
            'multi_match' => [
                'query' => 'brown bread',
                'fields' => ['title^2', 'body'],
                'type' => 'cross_fields',
                'operator' => 'and',
            ],
        ], MultiMatch::make('brown bread', ['title^2', 'body'], 'cross_fields', 'and')->toOpenSearchQuery());
    }
}
