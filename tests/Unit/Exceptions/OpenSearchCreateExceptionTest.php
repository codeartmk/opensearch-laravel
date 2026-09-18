<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Exceptions;

use Codeart\OpensearchLaravel\Exceptions\OpenSearchCreateException;
use Codeart\OpensearchLaravel\Exceptions\OpenSearchException;
use PHPUnit\Framework\TestCase;

class OpenSearchCreateExceptionTest extends TestCase
{
    public function testItReducesTheFailedItemsAndKeepsThemOutOfTheMessage()
    {
        $error = [
            'type' => 'strict_dynamic_mapping_exception',
            'reason' => 'mapping set to strict, dynamic introduction of [leaked_field] within [_doc] is not allowed',
        ];
        $response = [
            'took' => 5,
            'errors' => true,
            'items' => [
                ['index' => ['_index' => 'users', '_id' => '1', 'result' => 'created', 'status' => 201]],
                ['create' => ['_index' => 'users', '_id' => '2', 'status' => 409, 'error' => ['type' => 'version_conflict_engine_exception']]],
                ['update' => ['_index' => 'users', '_id' => '3', 'result' => 'updated', 'status' => 200]],
                ['index' => ['_index' => 'users', '_id' => '4', 'status' => 400, 'error' => $error]],
            ],
        ];

        $exception = new OpenSearchCreateException('users', $response, 102);

        $this->assertInstanceOf(OpenSearchException::class, $exception);
        $this->assertSame(
            "Bulk indexing into 'users' failed: 2 of 4 documents in the current chunk were rejected. 102 documents were indexed before the failure.",
            $exception->getMessage()
        );
        $this->assertSame([
            ['_id' => '2', 'status' => 409, 'error' => ['type' => 'version_conflict_engine_exception']],
            ['_id' => '4', 'status' => 400, 'error' => $error],
        ], $exception->getFailedItems());
        $this->assertSame(102, $exception->getIndexedCount());
        $this->assertSame($response, $exception->getResponse());
        $this->assertStringNotContainsString('leaked_field', $exception->getMessage());
    }

    public function testItHandlesAResponseWithoutItems()
    {
        $exception = new OpenSearchCreateException('users', ['errors' => true], 0);

        $this->assertSame([], $exception->getFailedItems());
        $this->assertSame(0, $exception->getIndexedCount());
        $this->assertSame(
            "Bulk indexing into 'users' failed: 0 of 0 documents in the current chunk were rejected. 0 documents were indexed before the failure.",
            $exception->getMessage()
        );
    }
}
