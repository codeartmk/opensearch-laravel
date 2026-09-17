<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\DisMax;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne;
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
}
