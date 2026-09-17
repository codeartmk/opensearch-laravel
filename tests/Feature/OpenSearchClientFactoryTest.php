<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Codeart\OpensearchLaravel\Tests\Mocks\MockOpenSearchable;
use Codeart\OpensearchLaravel\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use OpenSearch\Client;

class OpenSearchClientFactoryTest extends TestCase
{
    public function testOpensearchUsesTheFactoryBoundInTheContainer()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')
            ->once()
            ->andReturnUsing(fn($params) => $params);

        $this->mock(OpensearchClientFactory::class, function (MockInterface $factory) use ($client) {
            $factory->shouldReceive('createClient')->andReturn($client);
        });

        $this->assertSame([
            'index' => 'mockopensearchables',
            'size' => 10000,
            'body' => [],
        ], MockOpenSearchable::opensearch()->builder()->get());
    }

    public function testTheFactoryIsASingletonThatReusesItsClient()
    {
        $factory = $this->app->make(OpensearchClientFactory::class);

        $this->assertSame($factory, $this->app->make(OpensearchClientFactory::class));
        $this->assertSame($factory->createClient(), $factory->createClient());
    }

    public function testForgetClientMakesTheNextCallBuildANewClient()
    {
        $factory = $this->app->make(OpensearchClientFactory::class);
        $client = $factory->createClient();

        $factory->forgetClient();

        $this->assertNotSame($client, $factory->createClient());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
