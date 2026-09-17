<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Filter;
use Codeart\OpensearchLaravel\Search\SearchQueries\Must;
use Codeart\OpensearchLaravel\Search\SearchQueries\MustNot;
use Codeart\OpensearchLaravel\Search\SearchQueries\Should;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Exists;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class BoolQueryTest extends TestCase
{
    public function testBuildsABoolQueryWithEveryClauseAndOption()
    {
        $query = BoolQuery::make([
            Must::make(MatchOne::make('title', 'fox')),
            Should::make([Term::make('tags', 'fox'), Term::make('tags', 'dog')]),
            MustNot::make(Term::make('category', 'food')),
            Filter::make(Exists::make('price')),
            'minimum_should_match' => 1,
            'boost' => 1.5,
        ]);

        $this->assertEquals([
            'bool' => [
                'must' => ['match' => ['title' => 'fox']],
                'should' => [
                    ['term' => ['tags' => 'fox']],
                    ['term' => ['tags' => 'dog']],
                ],
                'must_not' => ['term' => ['category' => 'food']],
                'filter' => ['exists' => ['field' => 'price']],
                'minimum_should_match' => 1,
                'boost' => 1.5,
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testLeavesOutMinimumShouldMatchWithoutAShouldClause()
    {
        $query = BoolQuery::make([
            Must::make(MatchOne::make('title', 'fox')),
            'minimum_should_match' => 1,
        ]);

        $this->assertEquals([
            'bool' => [
                'must' => ['match' => ['title' => 'fox']],
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testStillAcceptsClausesUnderStringKeys()
    {
        $query = BoolQuery::make(['must' => Must::make(MatchOne::make('title', 'fox'))]);

        $this->assertEquals([
            'bool' => [
                'must' => ['match' => ['title' => 'fox']],
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testAcceptsSubclassesOfTheClauses()
    {
        $clause = new class(MatchOne::make('title', 'fox')) extends Must {};

        $this->assertEquals([
            'bool' => [
                'must' => ['match' => ['title' => 'fox']],
            ],
        ], BoolQuery::make([$clause])->toOpenSearchQuery());
    }

    public function testSendsAnEmptyBoolQueryAsAnObject()
    {
        $this->assertSame('{"bool":{}}', json_encode(BoolQuery::make([])->toOpenSearchQuery()));
        $this->assertSame('{"bool":{}}', json_encode(BoolQuery::make(['minimum_should_match' => 1])->toOpenSearchQuery()));
    }

    public function testThrowsWhenAQueryIsPassedWithoutAClause()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('BoolQuery accepts only Must, Should, MustNot and Filter clauses, ' . Term::class . ' given.');

        BoolQuery::make([Term::make('category', 'food')]);
    }

    public function testThrowsOnAnUnknownOption()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('BoolQuery accepts only the minimum_should_match and boost options, "minimum_should_matches" given.');

        BoolQuery::make([
            Should::make(Term::make('tags', 'fox')),
            'minimum_should_matches' => 1,
        ]);
    }

    public function testThrowsOnADuplicateClause()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('BoolQuery accepts only one Must clause.');

        BoolQuery::make([
            Must::make(Term::make('category', 'food')),
            Must::make(Term::make('tags', 'bread')),
        ]);
    }
}
