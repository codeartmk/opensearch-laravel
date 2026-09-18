<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Ids;
use PHPUnit\Framework\TestCase;

class IdsTest extends TestCase
{
    public function testBuildsAnIdsQueryFromAList()
    {
        $this->assertEquals([
            'ids' => [
                'values' => [34229, '9b2c7e1a-4f3d-4e8a-9c61-2d5f0a7b8e34'],
            ],
        ], Ids::make([34229, '9b2c7e1a-4f3d-4e8a-9c61-2d5f0a7b8e34'])->toOpenSearchQuery());
    }

    public function testWrapsASingleIntegerIdInAList()
    {
        $this->assertSame([
            'ids' => [
                'values' => [34229],
            ],
        ], Ids::make(34229)->toOpenSearchQuery());
    }

    public function testWrapsASingleStringIdInAList()
    {
        $this->assertSame([
            'ids' => [
                'values' => ['9b2c7e1a-4f3d-4e8a-9c61-2d5f0a7b8e34'],
            ],
        ], Ids::make('9b2c7e1a-4f3d-4e8a-9c61-2d5f0a7b8e34')->toOpenSearchQuery());
    }
}
