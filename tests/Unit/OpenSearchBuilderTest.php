<?php

namespace Codeart\OpensearchLaravel\Tests\Unit;

use Codeart\OpensearchLaravel\Aggregations\Aggregation;
use Codeart\OpensearchLaravel\Aggregations\Types\Terms;
use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Exceptions\OpenSearchException;
use Codeart\OpensearchLaravel\OpenSearchable;
use Codeart\OpensearchLaravel\OpenSearchBuilder;
use Codeart\OpensearchLaravel\Search\Query;
use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Must;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne;
use Codeart\OpensearchLaravel\Search\Sort;
use Codeart\OpensearchLaravel\Tests\TestCase;
use Mockery;
use OpenSearch\Client;

class OpenSearchBuilderTest extends TestCase
{
    private OpenSearchBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')
            ->andReturnUsing(fn($params) => $params);

        $model = Mockery::mock(OpenSearchable::class);
        $model->shouldReceive('openSearchIndexName')
            ->andReturn('posts');

        $this->builder = new OpenSearchBuilder($client, $model);
    }

    public function testTargetsThePrefixedIndex()
    {
        config(['opensearch-laravel.index_prefix' => 'local_']);

        $this->assertSame('local_posts', $this->builder->get()['index']);
    }

    public function testSendsOnlyTheIndexAndAnEmptyBodyWhenNoOptionsAreUsed()
    {
        $this->assertSame([
            'index' => 'posts',
            'body' => [],
        ], $this->builder->get());
    }

    public function testSendsSizeOnlyWhenItIsCalled()
    {
        $this->assertArrayNotHasKey('size', $this->builder->get());

        $this->assertSame([
            'index' => 'posts',
            'body' => [],
            'size' => 20,
        ], $this->builder->size(20)->get());
    }

    public function testSendsSizeZero()
    {
        $this->assertSame([
            'index' => 'posts',
            'body' => [],
            'size' => 0,
        ], $this->builder->size(0)->get());
    }

    public function testAddsFromSourceHighlightAndTrackTotalHits()
    {
        $parameters = $this->builder
            ->search([Query::make([MatchOne::make('title', 'fox')])])
            ->aggregations(Aggregation::make('categories', Terms::make('category')))
            ->size(20)
            ->from(40)
            ->source(['title', 'price'])
            ->highlight(['title' => ['fragment_size' => 50]], ['pre_tags' => ['<b>'], 'post_tags' => ['</b>']])
            ->trackTotalHits()
            ->get();

        $this->assertSame([
            'index' => 'posts',
            'body' => [
                'query' => ['match' => ['title' => 'fox']],
                'aggs' => [
                    'categories' => ['terms' => ['field' => 'category', 'size' => 10]],
                ],
                '_source' => ['title', 'price'],
                'highlight' => [
                    'pre_tags' => ['<b>'],
                    'post_tags' => ['</b>'],
                    'fields' => [
                        'title' => ['fragment_size' => 50],
                    ],
                ],
                'track_total_hits' => true,
            ],
            'size' => 20,
            'from' => 40,
        ], $parameters);
    }

    public function testSendsHighlightFieldsWithoutOptionsAsObjects()
    {
        $parameters = $this->builder->highlight(['title', 'body' => []])->get();

        $this->assertSame('{"fields":{"title":{},"body":{}}}', json_encode($parameters['body']['highlight']));
    }

    public function testCanDisableTheSourceAndLimitTotalHitTracking()
    {
        $parameters = $this->builder->source(false)->trackTotalHits(100)->get();

        $this->assertFalse($parameters['body']['_source']);
        $this->assertSame(100, $parameters['body']['track_total_hits']);
    }

    public function testFromIsSentWithoutSizeWhenSizeIsNotCalled()
    {
        $this->assertSame([
            'index' => 'posts',
            'body' => [],
            'from' => 10,
        ], $this->builder->from(10)->get());
    }

    public function testSizeIsSentBeforeFromWhicheverIsCalledFirst()
    {
        $this->assertSame(['index', 'body', 'size', 'from'], array_keys($this->builder->from(10)->size(20)->get()));
        $this->assertSame(['index', 'body', 'size', 'from'], array_keys($this->builder->size(20)->from(20)->get()));
    }

    public function testSearchAcceptsAQueryAndASortInAnyOrder()
    {
        $parameters = $this->builder
            ->search([Sort::make(['id' => 'desc']), Query::make([MatchOne::make('title', 'fox')])])
            ->get();

        $this->assertEquals([
            'sort' => ['id' => 'desc'],
            'query' => ['match' => ['title' => 'fox']],
        ], $parameters['body']);
    }

    public function testSearchThrowsOnAnEmptyList()
    {
        $this->expectException(InvalidSearchParametersException::class);

        $this->builder->search([]);
    }

    public function testSearchThrowsOnMoreThanTwoItems()
    {
        $this->expectException(InvalidSearchParametersException::class);

        $this->builder->search([
            Query::make([MatchOne::make('title', 'fox')]),
            Sort::make(['id' => 'desc']),
            Sort::make(['id' => 'asc']),
        ]);
    }

    public function testSearchThrowsWhenAQueryNodeIsPassedWithoutAQuery()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('The search method accepts only Query and Sort instances, ' . BoolQuery::class . ' given.');

        $this->builder->search([BoolQuery::make([Must::make(MatchOne::make('title', 'fox'))])]);
    }

    public function testSearchThrowsOnTwoQueries()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('The search method accepts only one Query.');

        $this->builder->search([Query::make([MatchOne::make('title', 'fox')]), Query::make([MatchOne::make('title', 'dog')])]);
    }

    public function testSearchThrowsOnTwoSorts()
    {
        $this->expectException(InvalidSearchParametersException::class);
        $this->expectExceptionMessage('The search method accepts only one Sort.');

        $this->builder->search([Sort::make(['id' => 'desc']), Sort::make(['id' => 'asc'])]);
    }

    public function testAnInvalidSearchKeepsThePreviousSearch()
    {
        $this->builder->search([Query::make([MatchOne::make('title', 'fox')])]);

        try {
            $this->builder->search(['not a query']);
        } catch (InvalidSearchParametersException) {
        }

        $this->assertEquals(['query' => ['match' => ['title' => 'fox']]], $this->builder->get()['body']);
    }

    public function testAggregationsThrowsOnInvalidInput()
    {
        $this->expectException(InvalidAggregationParametersException::class);

        $this->builder->aggregations([Terms::make('category')]);
    }

    public function testTheSearchExceptionCanBeCaughtAsAPackageAndAnInvalidArgumentException()
    {
        $exception = new InvalidSearchParametersException();

        $this->assertInstanceOf(OpenSearchException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
