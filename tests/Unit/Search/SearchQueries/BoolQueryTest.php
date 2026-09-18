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
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testAcceptsIntAndStringMinimumShouldMatch()
    {
        foreach ([1, -1, '1', '75%', '3<90%'] as $value) {
            $query = BoolQuery::make([
                Should::make(Term::make('tags', 'fox')),
                'minimum_should_match' => $value,
            ]);

            $this->assertSame($value, $query->toOpenSearchQuery()['bool']['minimum_should_match']);
        }
    }

    public function testAcceptsIntFloatAndNumericStringBoost()
    {
        foreach ([2, 1.5, '2', '1.5'] as $value) {
            $query = BoolQuery::make([
                Must::make(Term::make('tags', 'fox')),
                'boost' => $value,
            ]);

            $this->assertSame($value, $query->toOpenSearchQuery()['bool']['boost']);
        }
    }

    public function testLeavesOutOptionsSetToNull()
    {
        $query = BoolQuery::make([
            Should::make(Term::make('tags', 'fox')),
            'minimum_should_match' => null,
            'boost' => null,
        ]);

        $this->assertSame([
            'bool' => [
                'should' => ['term' => ['tags' => 'fox']],
            ],
        ], $query->toOpenSearchQuery());
        $this->assertSame('{"bool":{}}', json_encode(BoolQuery::make(['boost' => null])->toOpenSearchQuery()));
    }

    public static function invalidOptionProvider(): array
    {
        $must = Must::make(Term::make('tags', 'fox'));
        $should = Should::make(Term::make('tags', 'fox'));

        return [
            'Must under minimum_should_match' => ['minimum_should_match', $must, Must::class, 'an int or a string'],
            'Should under minimum_should_match' => ['minimum_should_match', $should, Should::class, 'an int or a string'],
            'MustNot under boost' => ['boost', MustNot::make(Term::make('tags', 'fox')), MustNot::class, 'an int, a float or a numeric string'],
            'Filter under boost' => ['boost', Filter::make(Exists::make('price')), Filter::class, 'an int, a float or a numeric string'],
            'float minimum_should_match' => ['minimum_should_match', 1.5, 'float', 'an int or a string'],
            'bool minimum_should_match' => ['minimum_should_match', true, 'bool', 'an int or a string'],
            'array minimum_should_match' => ['minimum_should_match', [1], 'array', 'an int or a string'],
            'non-numeric string boost' => ['boost', 'high', 'string', 'an int, a float or a numeric string'],
            'bool boost' => ['boost', true, 'bool', 'an int, a float or a numeric string'],
            'array boost' => ['boost', [1], 'array', 'an int, a float or a numeric string'],
        ];
    }

    #[DataProvider('invalidOptionProvider')]
    public function testThrowsOnAnInvalidOptionValue(string $key, mixed $value, string $type, string $expected)
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage("BoolQuery option \"$key\" must be $expected, $type given.");

        BoolQuery::make([
            Should::make(Term::make('tags', 'dog')),
            $key => $value,
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
