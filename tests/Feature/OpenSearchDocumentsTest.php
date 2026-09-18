<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

use Codeart\OpensearchLaravel\Exceptions\ModelException;
use Codeart\OpensearchLaravel\Exceptions\OpenSearchCreateException;
use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Codeart\OpensearchLaravel\OpenSearchDocuments;
use Codeart\OpensearchLaravel\Tests\Mocks\MockOpenSearchable;
use Codeart\OpensearchLaravel\Tests\TestCase;
use Mockery;
use OpenSearch\Client;

class OpenSearchDocumentsTest extends TestCase
{
    protected OpensearchClientFactory $clientFactory;
    protected Client $mockedClient;
    protected MockOpenSearchable $mockOpenSearchable;

    /**
     * What the mocked query returns from find(). Set it to null to simulate a missing model.
     */
    protected ?MockOpenSearchable $foundModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockedClient = Mockery::mock(Client::class);

        $this->clientFactory = $this->createMock(OpensearchClientFactory::class);
        $this->clientFactory->method('createClient')
            ->willReturn($this->mockedClient);

        $this->mockOpenSearchable = Mockery::mock(MockOpenSearchable::class)->makePartial();
        $this->mockOpenSearchable->shouldReceive('openSearchArray')
            ->andReturn(
                [
                    'id' => 1,
                    'foo' => [
                        'bar' => 'foobar'
                    ]
                ]
            );

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('chunk')
            ->andReturnUsing(function ($size, $callback) {
                $callback([
                    $this->mockOpenSearchable,
                    $this->mockOpenSearchable,
                    $this->mockOpenSearchable
                ]);
            });
        $queryMock->shouldReceive('with')
            ->andReturnSelf();
        $queryMock->shouldReceive('find')
            ->andReturnUsing(fn() => $this->foundModel);

        $this->mockOpenSearchable->shouldReceive('query')->andReturn($queryMock);

        $this->foundModel = $this->mockOpenSearchable;
    }

    public function testCreateAllWillThrowOpenSearchCreateException()
    {
        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldReceive('bulk')
            ->andReturnUsing(fn($params) => [
                'errors' => true
            ]);

        $this->expectException(OpenSearchCreateException::class);

        $os->createAll(fn($query) => $query->with('relationship'), 200);
    }

    public function testCreateAllWillReturnTrueIfNoErrors()
    {
        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldReceive('bulk')
            ->andReturnUsing(fn($params) => []);

        $this->assertTrue($os->createAll(fn($query) => $query->with('relationship')));
    }

    public function testCreateOrUpdateCreatesProperParameters()
    {
        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldReceive('update')
            ->andReturnUsing(fn($params) => $params);

        $result = $os->createOrUpdate(4, fn($query) => $query->with('relationship'));

        $expectedResult = [
            "id" => 1,
            "refresh" => true,
            "retry_on_conflict" => 5,
            "body" => [
                "doc" => [
                    "id" => 1,
                    "foo" => [
                        "bar" => "foobar"
                    ]
                ],
                "doc_as_upsert" => true
            ]
        ];

        foreach ($expectedResult as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testCreateOrUpdateThrowsWhenTheModelDoesNotExist()
    {
        $this->foundModel = null;

        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->expectException(ModelException::class);

        $os->createOrUpdate(404);
    }

    public function testDeleteCreatesProperParameters()
    {
        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldReceive('delete')
            ->andReturnUsing(fn($params) => $params);

        $expectedResults = [
            "index" => $this->mockOpenSearchable->openSearchIndexName(),
            "id" => 1
        ];

        $results = $os->delete(1);

        $this->assertEquals($expectedResults, $results);
    }

    public function testCreateAllTargetsThePrefixedIndex()
    {
        config(['opensearch-laravel.index_prefix' => 'local_']);

        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);
        $indexName = 'local_' . $this->mockOpenSearchable->openSearchIndexName();
        $sentBodies = [];

        $this->mockedClient->shouldReceive('bulk')
            ->andReturnUsing(function ($params) use (&$sentBodies) {
                $sentBodies[] = $params['body'];

                return [];
            });

        $os->createAll();

        $this->assertCount(1, $sentBodies);
        $this->assertSame(['_index' => $indexName, '_id' => 1], $sentBodies[0][0]['index']);
        $this->assertSame(['_index' => $indexName, '_id' => 1], $sentBodies[0][2]['index']);
        $this->assertSame(['_index' => $indexName, '_id' => 1], $sentBodies[0][4]['index']);
    }

    public function testCreateTargetsThePrefixedIndex()
    {
        config(['opensearch-laravel.index_prefix' => 'local_']);

        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn($params) => $params['index'] === 'local_' . $this->mockOpenSearchable->openSearchIndexName()))
            ->andReturn([]);

        $this->assertTrue($os->create(1));
    }

    public function testCreateOrUpdateAndDeleteTargetThePrefixedIndex()
    {
        config(['opensearch-laravel.index_prefix' => 'local_']);

        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);
        $indexName = 'local_' . $this->mockOpenSearchable->openSearchIndexName();

        $this->mockedClient->shouldReceive('update')
            ->andReturnUsing(fn($params) => $params);
        $this->mockedClient->shouldReceive('delete')
            ->andReturnUsing(fn($params) => $params);

        $this->assertSame($indexName, $os->createOrUpdate(1)['index']);
        $this->assertSame($indexName, $os->delete(1)['index']);
    }
}
