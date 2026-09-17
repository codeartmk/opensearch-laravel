<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\FunctionScore;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class FunctionScoreTest extends TestCase
{
    public function testBuildsAFunctionScoreQuery()
    {
        $functions = [
            ['field_value_factor' => ['field' => 'quantity', 'modifier' => 'log1p']],
        ];

        $this->assertEquals([
            'function_score' => [
                'functions' => $functions,
            ],
        ], FunctionScore::make($functions)->toOpenSearchQuery());
    }

    public function testBuildsAFunctionScoreQueryWithEveryOption()
    {
        $query = FunctionScore::make(
            functions: [
                ['filter' => Term::make('category', 'food'), 'weight' => 2],
                ['filter' => ['term' => ['category' => 'animals']], 'weight' => 3],
            ],
            query: MatchOne::make('title', 'quick'),
            scoreMode: 'sum',
            boostMode: 'multiply',
            maxBoost: 10,
            minScore: 0.5
        );

        $this->assertEquals([
            'function_score' => [
                'functions' => [
                    ['filter' => ['term' => ['category' => 'food']], 'weight' => 2],
                    ['filter' => ['term' => ['category' => 'animals']], 'weight' => 3],
                ],
                'query' => ['match' => ['title' => 'quick']],
                'score_mode' => 'sum',
                'boost_mode' => 'multiply',
                'max_boost' => 10,
                'min_score' => 0.5,
            ],
        ], $query->toOpenSearchQuery());
    }
}
