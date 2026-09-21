<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

use Codeart\OpensearchLaravel\OpenSearchDocuments;
use Codeart\OpensearchLaravel\Tests\Mocks\MockIndexedRecord;
use Codeart\OpensearchLaravel\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use OpenSearch\Client;

/**
 * Runs createAll() against a real in-memory SQLite table, so the paging is done by Eloquent itself.
 */
class OpenSearchDocumentsChunkingTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('mock_indexed_records', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        foreach (range(1, 10) as $number) {
            MockIndexedRecord::create(['name' => "record $number"]);
        }
    }

    public function testCreateAllIndexesEveryRowWhenRowsAreDeletedMidRun()
    {
        $client = Mockery::mock(Client::class);
        $sentIds = [];

        $client->shouldReceive('bulk')
            ->andReturnUsing(function ($params) use (&$sentIds) {
                foreach ($params['body'] as $line) {
                    if (isset($line['index'])) {
                        $sentIds[] = $line['index']['_id'];
                    }
                }

                // Delete a row from the page that was just sent. OFFSET paging would now skip a row.
                if (count($sentIds) === 3) {
                    MockIndexedRecord::query()->whereKey(1)->delete();
                }

                return [];
            });

        $os = new OpenSearchDocuments($client, new MockIndexedRecord());

        $this->assertTrue($os->createAll(size: 3));

        $this->assertSame(range(1, 10), $sentIds);
        $this->assertSame(range(2, 10), MockIndexedRecord::query()->pluck('id')->all());
    }
}
