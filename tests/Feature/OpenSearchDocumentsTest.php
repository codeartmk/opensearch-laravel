<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

use Codeart\OpensearchLaravel\Exceptions\ModelException;
use Codeart\OpensearchLaravel\Exceptions\OpenSearchCreateException;
use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Codeart\OpensearchLaravel\OpenSearchDocuments;
use Codeart\OpensearchLaravel\Tests\Mocks\MockOpenSearchable;
use Codeart\OpensearchLaravel\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * The chunks the mocked chunkById() hands to its callback, in order. Null means one chunk of three models.
     */
    protected ?array $chunks = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockedClient = Mockery::mock(Client::class);

        $this->clientFactory = $this->createStub(OpensearchClientFactory::class);
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
        $queryMock->shouldReceive('chunkById')
            ->andReturnUsing(function ($size, $callback) {
                $chunks = $this->chunks ?? [[
                    $this->mockOpenSearchable,
                    $this->mockOpenSearchable,
                    $this->mockOpenSearchable
                ]];

                foreach ($chunks as $chunk) {
                    $callback($chunk);
                }
            });
        $queryMock->shouldReceive('with')
            ->andReturnSelf();
        $queryMock->shouldReceive('find')
            ->andReturnUsing(fn($ids) => is_array($ids) ? new Collection(array_filter([$this->foundModel])) : $this->foundModel);

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

    public function testDocumentIdsComeFromTheModelsPrimaryKey()
    {
        $uuid = '9b2f6c1e-4d3a-4f8e-a2b7-5c0d1e9f3a64';
        $this->mockOpenSearchable->setKeyName('uuid');
        $this->mockOpenSearchable->setKeyType('string');
        $this->mockOpenSearchable->setAttribute('uuid', $uuid);

        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);
        $sentBodies = [];

        $this->mockedClient->shouldReceive('bulk')
            ->andReturnUsing(function ($params) use (&$sentBodies) {
                $sentBodies[] = $params['body'];

                return [];
            });
        $this->mockedClient->shouldReceive('create')
            ->andReturnUsing(function ($params) use (&$sentBodies) {
                $sentBodies[] = $params;

                return [];
            });
        $this->mockedClient->shouldReceive('update')
            ->andReturnUsing(fn($params) => $params);

        $os->createAll();
        $os->create([$uuid]);
        $os->create($uuid);

        $this->assertSame($uuid, $sentBodies[0][0]['index']['_id']);
        $this->assertSame($uuid, $sentBodies[0][2]['index']['_id']);
        $this->assertSame($uuid, $sentBodies[0][4]['index']['_id']);
        $this->assertSame($uuid, $sentBodies[1][0]['index']['_id']);
        $this->assertSame($uuid, $sentBodies[2]['id']);
        $this->assertSame($uuid, $os->createOrUpdate($uuid)['id']);
    }

    public function testCreateOrUpdateAndDeleteAcceptStringIds()
    {
        $uuid = '9b2f6c1e-4d3a-4f8e-a2b7-5c0d1e9f3a64';
        $this->mockOpenSearchable->setKeyName('uuid');
        $this->mockOpenSearchable->setKeyType('string');
        $this->mockOpenSearchable->setAttribute('uuid', $uuid);

        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldReceive('update')
            ->andReturnUsing(fn($params) => $params);
        $this->mockedClient->shouldReceive('delete')
            ->andReturnUsing(fn($params) => $params);

        $this->assertSame($uuid, $os->createOrUpdate($uuid)['id']);
        $this->assertSame(
            ['index' => $this->mockOpenSearchable->openSearchIndexName(), 'id' => $uuid],
            $os->delete($uuid)
        );
    }

    public function testCreateWithASingleIdThrowsWhenTheModelDoesNotExist()
    {
        $this->foundModel = null;

        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldNotReceive('create');
        $this->mockedClient->shouldNotReceive('bulk');

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('No model found with id:999 for index:' . $this->mockOpenSearchable->openSearchIndexName() . '.');

        $os->create(999);
    }

    public function testCreateWithASingleIdUsesTheCreateEndpointAndAnArrayUsesBulkIndex()
    {
        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);
        $indexName = $this->mockOpenSearchable->openSearchIndexName();

        $this->mockedClient->shouldReceive('create')
            ->once()
            ->with([
                'index' => $indexName,
                'id' => 1,
                'refresh' => true,
                'body' => ['id' => 1, 'foo' => ['bar' => 'foobar']],
            ])
            ->andReturn([]);
        $this->mockedClient->shouldReceive('bulk')
            ->once()
            ->with([
                'body' => [
                    ['index' => ['_index' => $indexName, '_id' => 1]],
                    ['id' => 1, 'foo' => ['bar' => 'foobar']],
                ],
            ])
            ->andReturn([]);

        $this->assertTrue($os->create(1));
        $this->assertTrue($os->create([1]));
    }

    public function testCreateAllReportsTheFailedItemsOfAPartiallyFailedChunk()
    {
        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);
        $indexName = $this->mockOpenSearchable->openSearchIndexName();

        $this->mockedClient->shouldReceive('bulk')
            ->andReturn($this->bulkResponse($indexName, [201, 400, 400]));

        try {
            $os->createAll();
            $this->fail('OpenSearchCreateException was not thrown.');
        } catch (OpenSearchCreateException $e) {
            $this->assertSame(
                "Bulk indexing into '$indexName' failed: 2 of 3 documents in the current chunk were rejected. 1 document was indexed before the failure.",
                $e->getMessage()
            );
            $this->assertSame([
                ['_id' => '2', 'status' => 400, 'error' => $this->bulkError('2')],
                ['_id' => '3', 'status' => 400, 'error' => $this->bulkError('3')],
            ], $e->getFailedItems());
            $this->assertSame(1, $e->getIndexedCount());
            $this->assertSame($this->bulkResponse($indexName, [201, 400, 400]), $e->getResponse());
            $this->assertStringNotContainsString('foobar', $e->getMessage());
            $this->assertStringNotContainsString('SECRET_FIELD_VALUE', $e->getMessage());
        }
    }

    public function testCreateAllCountsTheDocumentsIndexedByEarlierChunks()
    {
        $this->chunks = [
            [$this->mockOpenSearchable, $this->mockOpenSearchable],
            [$this->mockOpenSearchable, $this->mockOpenSearchable, $this->mockOpenSearchable],
            [$this->mockOpenSearchable],
        ];

        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);
        $indexName = $this->mockOpenSearchable->openSearchIndexName();

        $this->mockedClient->shouldReceive('bulk')
            ->twice()
            ->andReturn(
                $this->bulkResponse($indexName, [201, 200]),
                $this->bulkResponse($indexName, [201, 400, 201])
            );

        try {
            $os->createAll(null, 2);
            $this->fail('OpenSearchCreateException was not thrown.');
        } catch (OpenSearchCreateException $e) {
            $this->assertSame(4, $e->getIndexedCount());
            $this->assertCount(1, $e->getFailedItems());
            $this->assertSame('2', $e->getFailedItems()[0]['_id']);
            $this->assertStringEndsWith('1 of 3 documents in the current chunk was rejected. 4 documents were indexed before the failure.', $e->getMessage());
        }
    }

    public function testCreateWithAnArrayOfIdsReportsTheFailedItems()
    {
        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);
        $indexName = $this->mockOpenSearchable->openSearchIndexName();

        $this->mockedClient->shouldReceive('bulk')
            ->andReturn($this->bulkResponse($indexName, [400]));

        try {
            $os->create([1]);
            $this->fail('OpenSearchCreateException was not thrown.');
        } catch (OpenSearchCreateException $e) {
            $this->assertSame(0, $e->getIndexedCount());
            $this->assertSame([['_id' => '1', 'status' => 400, 'error' => $this->bulkError('1')]], $e->getFailedItems());
        }
    }

    /**
     * A bulk response shaped like the one OpenSearch 3.0 returns, with one item per status. Ids count up from 1.
     */
    private function bulkResponse(string $indexName, array $statuses): array
    {
        $items = [];

        foreach ($statuses as $position => $status) {
            $id = (string)($position + 1);

            if ($status >= 300) {
                $items[] = ['index' => ['_index' => $indexName, '_id' => $id, 'status' => $status, 'error' => $this->bulkError($id)]];

                continue;
            }

            $items[] = [
                'index' => [
                    '_index' => $indexName,
                    '_id' => $id,
                    '_version' => 1,
                    'result' => $status === 201 ? 'created' : 'updated',
                    '_shards' => ['total' => 2, 'successful' => 1, 'failed' => 0],
                    '_seq_no' => $position,
                    '_primary_term' => 1,
                    'status' => $status,
                ],
            ];
        }

        return [
            'took' => 22,
            'errors' => in_array(true, array_map(fn($status) => $status >= 300, $statuses), true),
            'items' => $items,
        ];
    }

    private function bulkError(string $id): array
    {
        return [
            'type' => 'mapper_parsing_exception',
            'reason' => "failed to parse field [n] of type [integer] in document with id '$id'. Preview of field's value: 'SECRET_FIELD_VALUE'",
            'caused_by' => [
                'type' => 'number_format_exception',
                'reason' => 'For input string: "SECRET_FIELD_VALUE"',
            ],
        ];
    }
}
