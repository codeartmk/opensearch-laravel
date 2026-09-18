<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Codeart\OpensearchLaravel\Tests\Mocks\MockOpenSearchable;
use Codeart\OpensearchLaravel\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use OpenSearch\Client;
use ReflectionMethod;

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

    public function testTheClientOptionsAreReadFromTheConfig()
    {
        config([
            'opensearch-laravel.host' => 'https://search.example.com:9200',
            'opensearch-laravel.username' => 'user',
            'opensearch-laravel.password' => 'secret',
            'opensearch-laravel.ssl_verification' => true,
        ]);

        $this->assertSame([
            'base_uri' => 'https://search.example.com:9200',
            'verify' => true,
            'auth' => ['user', 'secret'],
        ], $this->clientOptions());
    }

    public function testAuthIsLeftOutWhenNoUsernameIsConfigured()
    {
        foreach ([null, ''] as $username) {
            config([
                'opensearch-laravel.host' => 'http://localhost:9200',
                'opensearch-laravel.username' => $username,
                'opensearch-laravel.password' => 'secret',
                'opensearch-laravel.ssl_verification' => false,
            ]);

            $this->assertSame([
                'base_uri' => 'http://localhost:9200',
                'verify' => false,
            ], $this->clientOptions());
        }
    }

    private function clientOptions(): array
    {
        return (new ReflectionMethod(OpensearchClientFactory::class, 'options'))
            ->invoke($this->app->make(OpensearchClientFactory::class));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
