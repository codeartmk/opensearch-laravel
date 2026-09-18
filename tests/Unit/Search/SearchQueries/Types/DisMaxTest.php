<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Sum;
use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Must;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\DisMax;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class DisMaxTest extends TestCase
{
    public function testBuildsADisMaxQuery()
    {
        $this->assertEquals([
            'dis_max' => [
                'queries' => [
                    ['match' => ['title' => 'bread']],
                    ['match' => ['body' => 'bread']],
                ],
            ],
        ], DisMax::make([MatchOne::make('title', 'bread'), MatchOne::make('body', 'bread')])->toOpenSearchQuery());
    }

    public function testBuildsADisMaxQueryWithTieBreaker()
    {
        $query = DisMax::make(['title' => MatchOne::make('title', 'bread')], 0.7);

        $this->assertEquals([
            'dis_max' => [
                'queries' => [
                    ['match' => ['title' => 'bread']],
                ],
                'tie_breaker' => 0.7,
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testAcceptsABoolQuery()
    {
        $query = DisMax::make([BoolQuery::make([Must::make(Term::make('status', 'active'))])]);

        $this->assertEquals([
            'dis_max' => [
                'queries' => [
                    ['bool' => ['must' => ['term' => ['status' => 'active']]]],
                ],
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testThrowsForAnEmptyList()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('DisMax requires at least one query.');

        DisMax::make([]);
    }

    public function testThrowsForAnItemThatIsNotAQuery()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('DisMax accepts only SearchQueryType or BoolQuery queries, ' . Sum::class . ' given at key 1.');

        DisMax::make([MatchOne::make('title', 'bread'), Sum::make('price')]);
    }

    public function testThrowsForARawArrayNamingItsKey()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage("DisMax accepts only SearchQueryType or BoolQuery queries, array given at key 'body'.");

        DisMax::make(['body' => ['match' => ['body' => 'bread']]]);
    }
}
