<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Codeart\OpensearchLaravel\OpenSearchHealth;
use Codeart\OpensearchLaravel\Tests\TestCase;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Mockery;
use Mockery\MockInterface;
use OpenSearch\Client;
use OpenSearch\Exception\NotFoundHttpException;
use OpenSearch\Exception\UnauthorizedHttpException;
use OpenSearch\Namespaces\ClusterNamespace;
use OpenSearch\Namespaces\IndicesNamespace;

class OpenSearchHealthTest extends TestCase
{
    protected MockInterface $mockedClient;
    protected MockInterface $cluster;
    protected MockInterface $indices;
    protected OpensearchClientFactory $clientFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->cluster = Mockery::mock(ClusterNamespace::class);
        $this->indices = Mockery::mock(IndicesNamespace::class);

        $this->mockedClient = Mockery::mock(Client::class);
        $this->mockedClient->shouldReceive('cluster')->andReturn($this->cluster);
        $this->mockedClient->shouldReceive('indices')->andReturn($this->indices);

        $this->clientFactory = $this->createStub(OpensearchClientFactory::class);
        $this->clientFactory->method('createClient')
            ->willReturn($this->mockedClient);
    }

    public function testTheContainerResolvesTheHealthService()
    {
        $this->assertInstanceOf(OpenSearchHealth::class, $this->app->make(OpenSearchHealth::class));
    }

    public function testIsReachableReturnsTrueWhenThePingSucceeds()
    {
        $this->mockedClient->shouldReceive('ping')->once()->andReturn(true);

        $this->assertTrue($this->health()->isReachable());
    }

    public function testIsReachableReturnsFalseWhenTheConnectionFails()
    {
        $this->mockedClient->shouldReceive('ping')->once()->andThrow($this->connectException());

        $this->assertFalse($this->health()->isReachable());
    }

    public function testIsReachableReturnsFalseOnAnHttpError()
    {
        $this->mockedClient->shouldReceive('ping')->once()->andThrow(new UnauthorizedHttpException());

        $this->assertFalse($this->health()->isReachable());
    }

    public function testClusterReturnsTheRawHealthResponse()
    {
        $this->cluster->shouldReceive('health')->once()->withNoArgs()->andReturn($this->clusterHealth('yellow'));

        $this->assertSame($this->clusterHealth('yellow'), $this->health()->cluster());
    }

    public function testIndexMergesHealthStatsAndSettings()
    {
        $this->mockIndex('local_users', [
            'number_of_shards' => '2',
            'number_of_replicas' => '0',
            'refresh_interval' => '5s',
            'max_result_window' => '50000',
        ]);

        $this->assertSame([
            'index' => 'local_users',
            'status' => 'green',
            'docs_count' => 1,
            'store_size_in_bytes' => 3566,
            'number_of_shards' => 2,
            'number_of_replicas' => 0,
            'refresh_interval' => '5s',
            'max_result_window' => 50000,
        ], $this->health()->index('local_users'));
    }

    public function testIndexReturnsNullForSettingsLeftToTheClusterDefault()
    {
        $this->mockIndex('local_users', [
            'number_of_shards' => '1',
            'number_of_replicas' => '1',
        ]);

        $index = $this->health()->index('local_users');

        $this->assertNull($index['refresh_interval']);
        $this->assertNull($index['max_result_window']);
        $this->assertSame(1, $index['number_of_shards']);
    }

    public function testIndexReadsTheSettingsOfTheConcreteIndexBehindAnAlias()
    {
        $this->mockIndex('users', ['max_result_window' => '50000'], 'users_v2');

        $this->assertSame(50000, $this->health()->index('users')['max_result_window']);
    }

    public function testReportSummarisesAReachableCluster()
    {
        $this->mockedClient->shouldReceive('ping')->andReturn(true);
        $this->mockedClient->shouldReceive('info')->andReturn(['version' => ['number' => '3.0.0']]);
        $this->cluster->shouldReceive('health')->withNoArgs()->andReturn($this->clusterHealth('yellow'));
        $this->mockIndex('local_users', ['number_of_shards' => '1', 'number_of_replicas' => '1']);
        $this->indices->shouldReceive('getSettings')
            ->with(['index' => 'local_missing'])
            ->andThrow(new NotFoundHttpException());

        $report = $this->health()->report(['local_users', 'local_missing']);

        $this->assertTrue($report['reachable']);
        $this->assertSame($this->clusterHealth('yellow'), $report['cluster']);
        $this->assertSame('3.0.0', $report['version']);
        $this->assertSame('green', $report['indices']['local_users']['status']);
        $this->assertArrayHasKey('local_missing', $report['indices']);
        $this->assertNull($report['indices']['local_missing']);
    }

    public function testReportDoesNotThrowWhenTheClusterIsUnreachable()
    {
        $this->mockedClient->shouldReceive('ping')->andThrow($this->connectException());

        $this->assertSame([
            'reachable' => false,
            'cluster' => null,
            'version' => null,
            'indices' => ['local_users' => null],
        ], $this->health()->report(['local_users']));
    }

    public function testReportDoesNotThrowWhenTheConnectionDropsAfterThePing()
    {
        $this->mockedClient->shouldReceive('ping')->andReturn(true);
        $this->cluster->shouldReceive('health')->andThrow($this->connectException());

        $this->assertFalse($this->health()->report()['reachable']);
    }

    private function health(): OpenSearchHealth
    {
        return new OpenSearchHealth($this->clientFactory);
    }

    /**
     * Mocks the three per-index responses, trimmed from real OpenSearch 3.0 responses.
     */
    private function mockIndex(string $indexName, array $settings, ?string $concreteName = null): void
    {
        $this->indices->shouldReceive('getSettings')
            ->with(['index' => $indexName])
            ->andReturn([
                $concreteName ?? $indexName => [
                    'settings' => [
                        'index' => $settings + [
                            'provided_name' => $concreteName ?? $indexName,
                            'creation_date' => '1789726452185',
                            'uuid' => 'B9IlRH_lSp2jO3LM94K2qw',
                        ],
                    ],
                ],
            ]);

        $this->cluster->shouldReceive('health')
            ->with(['index' => $indexName])
            ->andReturn($this->clusterHealth('green'));

        $this->indices->shouldReceive('stats')
            ->with(['index' => $indexName])
            ->andReturn([
                '_shards' => ['total' => 2, 'successful' => 2, 'failed' => 0],
                '_all' => [
                    'primaries' => [
                        'docs' => ['count' => 1, 'deleted' => 0],
                        'store' => ['size_in_bytes' => 3566, 'reserved_in_bytes' => 0],
                    ],
                    'total' => [
                        'docs' => ['count' => 1, 'deleted' => 0],
                        'store' => ['size_in_bytes' => 3566, 'reserved_in_bytes' => 0],
                    ],
                ],
            ]);
    }

    private function clusterHealth(string $status): array
    {
        return [
            'cluster_name' => 'opensearch-cluster',
            'status' => $status,
            'timed_out' => false,
            'number_of_nodes' => 2,
            'number_of_data_nodes' => 2,
            'active_primary_shards' => 15,
            'active_shards' => 16,
            'unassigned_shards' => 11,
            'active_shards_percent_as_number' => 59.25925925925925,
        ];
    }

    private function connectException(): ConnectException
    {
        return new ConnectException('Connection refused', new Request('HEAD', 'http://localhost:9299'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
