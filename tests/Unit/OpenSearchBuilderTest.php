<?php

namespace Codeart\OpensearchLaravel\Tests\Unit;

use Codeart\OpensearchLaravel\Aggregations\Aggregation;
use Codeart\OpensearchLaravel\Aggregations\Types\Terms;
use Codeart\OpensearchLaravel\OpenSearchable;
use Codeart\OpensearchLaravel\OpenSearchBuilder;
use Codeart\OpensearchLaravel\Search\Query;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne;
use Mockery;
use OpenSearch\Client;
use PHPUnit\Framework\TestCase;

class OpenSearchBuilderTest extends TestCase
{
    private OpenSearchBuilder $builder;

    public function setUp(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')
            ->andReturnUsing(fn($params) => $params);

        $model = Mockery::mock(OpenSearchable::class);
        $model->shouldReceive('openSearchIndexName')
            ->andReturn('posts');

        $this->builder = new OpenSearchBuilder($client, $model);
    }

    public function testSendsTheSameRequestAsBeforeWhenNoNewOptionsAreUsed()
    {
        $this->assertSame([
            'index' => 'posts',
            'size' => 10000,
            'body' => [],
        ], $this->builder->get());
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

        $this->assertEquals([
            'index' => 'posts',
            'size' => 20,
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
