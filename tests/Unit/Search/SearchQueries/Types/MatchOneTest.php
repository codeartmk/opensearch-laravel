<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne;
use PHPUnit\Framework\TestCase;

class MatchOneTest extends TestCase
{
    public function testBuildsAMatchQuery()
    {
        $this->assertEquals([
            'match' => [
                'title' => 'wind',
            ],
        ], MatchOne::make('title', 'wind')->toOpenSearchQuery());
    }

    public function testBuildsAMatchQueryWithAnOptionsObject()
    {
        $this->assertEquals([
            'match' => [
                'title' => [
                    'query' => 'wind',
                    'operator' => 'and',
                ],
            ],
        ], MatchOne::make('title', ['query' => 'wind', 'operator' => 'and'])->toOpenSearchQuery());
    }
}
