<?php

namespace Codeart\OpensearchLaravel\Tests\Unit;

use Codeart\OpensearchLaravel\IndexNameResolver;
use Codeart\OpensearchLaravel\OpenSearchable;
use Codeart\OpensearchLaravel\Tests\TestCase;
use Mockery;

class IndexNameResolverTest extends TestCase
{
    private OpenSearchable $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = Mockery::mock(OpenSearchable::class);
        $this->model->shouldReceive('openSearchIndexName')
            ->andReturn('users');
    }

    public function testReturnsTheBaseNameWhenNoPrefixIsConfigured()
    {
        $this->assertSame('', config('opensearch-laravel.index_prefix'));
        $this->assertSame('users', IndexNameResolver::resolve($this->model));
    }

    public function testPrependsThePrefixVerbatim()
    {
        config(['opensearch-laravel.index_prefix' => 'local_']);

        $this->assertSame('local_users', IndexNameResolver::resolve($this->model));
    }

    public function testDoesNotAddASeparator()
    {
        config(['opensearch-laravel.index_prefix' => 'staging']);

        $this->assertSame('stagingusers', IndexNameResolver::resolve($this->model));
    }

    public function testTreatsANullPrefixAsNoPrefix()
    {
        config(['opensearch-laravel.index_prefix' => null]);

        $this->assertSame('users', IndexNameResolver::resolve($this->model));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
