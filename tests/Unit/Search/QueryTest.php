<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\Query;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Must;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Range;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use Codeart\OpensearchLaravel\Search\Sort;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testWrapsASingleQueryNode()
    {
        $this->assertEquals([
            'query' => ['term' => ['category' => 'food']],
        ], Query::make([Term::make('category', 'food')])->toOpenSearchQuery());
    }

    public function testWrapsABoolQuery()
    {
        $query = Query::make([BoolQuery::make([Must::make(Term::make('category', 'food'))])]);

        $this->assertEquals([
            'query' => [
                'bool' => [
                    'must' => ['term' => ['category' => 'food']],
                ],
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testAcceptsACustomQueryType()
    {
        $custom = new class implements SearchQueryType, OpenSearchQuery {
            public function toOpenSearchQuery(): array
            {
                return ['span_term' => ['title' => 'fox']];
            }
        };

        $this->assertEquals([
            'query' => ['span_term' => ['title' => 'fox']],
        ], Query::make([$custom])->toOpenSearchQuery());
    }

    public function testAcceptsAStringKey()
    {
        $this->assertEquals([
            'query' => ['term' => ['category' => 'food']],
        ], Query::make(['root' => Term::make('category', 'food')])->toOpenSearchQuery());
    }

    public function testThrowsOnAnEmptyList()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('Query requires a root query.');

        Query::make([]);
    }

    public function testThrowsOnMoreThanOneRootQuery()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('Query accepts only one root query. Combine conditions in a BoolQuery with Must or Filter clauses.');

        Query::make([Term::make('category', 'food'), Range::make('price', ['gte' => 10])]);
    }

    public function testThrowsWhenTheItemIsNotAQuery()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('Query accepts only a SearchQueryType or BoolQuery, ' . Sort::class . ' given.');

        Query::make([Sort::make(['id' => 'desc'])]);
    }

    public function testThrowsWhenTheItemIsNotAnObject()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('Query accepts only a SearchQueryType or BoolQuery, string given.');

        Query::make(['category' => 'food']);
    }
}
