<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolClause;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Filter;
use Codeart\OpensearchLaravel\Search\SearchQueries\Must;
use Codeart\OpensearchLaravel\Search\SearchQueries\MustNot;
use Codeart\OpensearchLaravel\Search\SearchQueries\Should;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use Codeart\OpensearchLaravel\Search\Sort;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BoolClauseTest extends TestCase
{
    public static function clauses(): array
    {
        return [
            'must' => [Must::class],
            'should' => [Should::class],
            'must not' => [MustNot::class],
            'filter' => [Filter::class],
        ];
    }

    #[DataProvider('clauses')]
    public function testMakeReturnsTheClauseItself(string $clause)
    {
        $instance = $clause::make(Term::make('category', 'food'));

        $this->assertInstanceOf($clause, $instance);
        $this->assertInstanceOf(BoolClause::class, $instance);
    }

    #[DataProvider('clauses')]
    public function testReturnsASingleQueryAsIs(string $clause)
    {
        $this->assertEquals(
            ['term' => ['category' => 'food']],
            $clause::make(Term::make('category', 'food'))->toOpenSearchQuery()
        );
    }

    #[DataProvider('clauses')]
    public function testReturnsAListOfQueriesAsAList(string $clause)
    {
        $this->assertEquals(
            [
                ['term' => ['category' => 'food']],
                ['term' => ['tags' => 'bread']],
            ],
            $clause::make([Term::make('category', 'food'), Term::make('tags', 'bread')])->toOpenSearchQuery()
        );
    }

    #[DataProvider('clauses')]
    public function testAcceptsABoolQueryOnItsOwnAndInAList(string $clause)
    {
        $boolQuery = BoolQuery::make([Should::make([Term::make('category', 'food'), Term::make('category', 'animals')])]);
        $expected = [
            'bool' => [
                'should' => [
                    ['term' => ['category' => 'food']],
                    ['term' => ['category' => 'animals']],
                ],
            ],
        ];

        $this->assertEquals($expected, $clause::make($boolQuery)->toOpenSearchQuery());
        $this->assertEquals([$expected], $clause::make([$boolQuery])->toOpenSearchQuery());
    }

    #[DataProvider('clauses')]
    public function testAcceptsAnEmptyList(string $clause)
    {
        $this->assertSame([], $clause::make([])->toOpenSearchQuery());
    }

    #[DataProvider('clauses')]
    public function testThrowsWhenAListItemIsNotAQuery(string $clause)
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage(class_basename($clause) . ' accepts only SearchQueryType or BoolQuery items, ' . Sort::class . ' given.');

        $clause::make([Term::make('category', 'food'), Sort::make(['id' => 'desc'])]);
    }

    #[DataProvider('clauses')]
    public function testThrowsWhenAListItemIsNotAnObject(string $clause)
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage(class_basename($clause) . ' accepts only SearchQueryType or BoolQuery items, string given.');

        $clause::make(['category' => 'food']);
    }
}
