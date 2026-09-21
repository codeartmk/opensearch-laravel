<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Prefix;
use PHPUnit\Framework\TestCase;

class PrefixTest extends TestCase
{
    public function testBuildsAPrefixQuery()
    {
        $this->assertSame([
            'prefix' => [
                'name' => [
                    'value' => 'joh',
                    'case_insensitive' => false,
                ],
            ],
        ], Prefix::make('name', 'joh')->toOpenSearchQuery());
    }

    public function testBuildsAPrefixQueryCaseInsensitive()
    {
        $this->assertSame([
            'prefix' => [
                'name' => [
                    'value' => 'joh',
                    'case_insensitive' => true,
                ],
            ],
        ], Prefix::make('name', 'joh', true)->toOpenSearchQuery());
    }
}
