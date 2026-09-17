<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\ConstantScore;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class ConstantScoreTest extends TestCase
{
    public function testBuildsAConstantScoreQuery()
    {
        $this->assertEquals([
            'constant_score' => [
                'filter' => ['term' => ['category' => 'food']],
            ],
        ], ConstantScore::make(Term::make('category', 'food'))->toOpenSearchQuery());
    }

    public function testBuildsAConstantScoreQueryWithBoost()
    {
        $this->assertEquals([
            'constant_score' => [
                'filter' => ['term' => ['category' => 'food']],
                'boost' => 1.5,
            ],
        ], ConstantScore::make(Term::make('category', 'food'), 1.5)->toOpenSearchQuery());
    }
}
