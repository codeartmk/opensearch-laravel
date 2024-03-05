<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

use Codeart\OpensearchLaravel\Exceptions\IndexAlreadyExistException;
use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Codeart\OpensearchLaravel\OpenSearchable;
use Codeart\OpensearchLaravel\OpenSearchIndices;
use Codeart\OpensearchLaravel\Tests\Mocks\MockOpenSearchable;
use Mockery;
use OpenSearch\Client;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class OpenSearchIndicesTest extends TestCase {

    protected OpensearchClientFactory $clientFactory;
    protected Client $mockedClient;
    protected MockOpenSearchable $mockOpenSearchable;

    public function setUp(): void
    {
        $this->mockedClient = Mockery::mock(Client::class);
        $this->mockedClient->shouldReceive('indices->create')
            ->andReturnUsing(fn($params) => $params);

        $this->clientFactory = $this->createMock(OpensearchClientFactory::class);
        $this->clientFactory->method('createClient')
            ->willReturn($this->mockedClient);

        $this->mockOpenSearchable = Mockery::mock(MockOpenSearchable::class)->makePartial();
        $this->mockOpenSearchable->shouldReceive('openSearchMapping')
            ->andReturn(
                [
                    'mock' => 'mappings',
                    'foo' => [
                        'bar' => 'foobar'
                    ]
                ]
            );
    }

    public function testCreateIndexWillThrowIndexAlreadyExistsException() {
        $os = new OpenSearchIndices($this->clientFactory->createClient(), new MockOpenSearchable());

        $this->mockedClient->shouldReceive('indices->exists')
            ->once()
            ->andReturn(true);

        $this->expectException(IndexAlreadyExistException::class);

        $os->create();
    }

    /**
     * @throws IndexAlreadyExistException
     */
    public function testCreateIndexCreatesProperParameters()
    {
        $os = new OpenSearchIndices($this->clientFactory->createClient(), $this->mockOpenSearchable);

        $this->mockedClient->shouldReceive('indices->exists')
            ->once()
            ->andReturn(false);

        $config = [
            'number_of_shards' => 3,
            'number_of_replicas' => 2,
            'refresh_interval' => '5s'
        ];

        $response = [
            "index" => $this->mockOpenSearchable->openSearchIndexName(),
            "body" => [
                "settings" => $config,
                "mappings" => $this->mockOpenSearchable->openSearchMapping()
            ]
        ];

        $this->assertEquals($response, $os->create($config));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}