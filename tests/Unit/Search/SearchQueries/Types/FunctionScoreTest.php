<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\Sum;
use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Filter;
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

    public function testAcceptsAnEmptyListOfFunctions()
    {
        $this->assertSame('{"function_score":{"functions":[]}}', json_encode(FunctionScore::make([])->toOpenSearchQuery()));
    }

    public function testAcceptsABoolQueryAndARawArrayAsAFilter()
    {
        $query = FunctionScore::make([
            ['filter' => BoolQuery::make([Filter::make(Term::make('category', 'food'))]), 'weight' => 2],
            ['filter' => ['term' => ['category' => 'animals']], 'weight' => 3],
        ]);

        $this->assertEquals([
            'function_score' => [
                'functions' => [
                    ['filter' => ['bool' => ['filter' => ['term' => ['category' => 'food']]]], 'weight' => 2],
                    ['filter' => ['term' => ['category' => 'animals']], 'weight' => 3],
                ],
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testThrowsForAFunctionThatIsNotAnArray()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('FunctionScore accepts only arrays as functions, ' . Term::class . ' given at key 0.');

        FunctionScore::make([Term::make('category', 'food')]);
    }

    public function testThrowsForAFilterObjectThatIsNotAQuery()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage("FunctionScore accepts only a SearchQueryType, a BoolQuery or an array as a function filter, " . Sum::class . " given at key 'boost'.");

        FunctionScore::make(['boost' => ['filter' => Sum::make('price'), 'weight' => 2]]);
    }
}
