<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Boosting;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class BoostingTest extends TestCase
{
    public function testBuildsABoostingQuery()
    {
        $query = Boosting::make(MatchOne::make('title', 'fox'), Term::make('category', 'animals'), 0.2);

        $this->assertEquals([
            'boosting' => [
                'positive' => ['match' => ['title' => 'fox']],
                'negative' => ['term' => ['category' => 'animals']],
                'negative_boost' => 0.2,
            ],
        ], $query->toOpenSearchQuery());
    }
}
