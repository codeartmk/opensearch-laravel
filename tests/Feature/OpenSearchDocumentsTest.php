<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

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

    public function setUp(): void
    {
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
            ->andReturn($this->mockOpenSearchable);

        $this->mockOpenSearchable->shouldReceive('query')->andReturn($queryMock);
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

        $this->mockedClient->shouldReceive('create')
            ->andReturnUsing(fn($params) => $params);

        $result = $os->createOrUpdate(4, fn($query) => $query->with('relationship'));

        $expectedResult = [
            "index" => 'mockery_1_codeart_opensearchlaravel_tests_mocks_mockopensearchables',
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

        $this->assertEquals($expectedResult, $result);
    }

    public function testDeleteCreatesProperParameters()
    {
        $os = new OpenSearchDocuments($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldReceive('delete')
            ->andReturnUsing(fn($params) => $params);

        $expectedResults = [
            "index" => "mockery_1_codeart_opensearchlaravel_tests_mocks_mockopensearchables",
            "id" => 1
        ];

        $results = $os->delete(1);

        $this->assertEquals($expectedResults, $results);
    }
}