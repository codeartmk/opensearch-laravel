<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Wildcard;
use PHPUnit\Framework\TestCase;

class WildcardTest extends TestCase
{
    public function testBuildsAWildcardQuery()
    {
        $this->assertSame([
            'wildcard' => [
                'name' => [
                    'value' => 'jo*n',
                    'case_insensitive' => false,
                ],
            ],
        ], Wildcard::make('name', 'jo*n')->toOpenSearchQuery());
    }

    public function testBuildsAWildcardQueryCaseInsensitive()
    {
        $this->assertSame([
            'wildcard' => [
                'name' => [
                    'value' => 'jo*n',
                    'case_insensitive' => true,
                ],
            ],
        ], Wildcard::make('name', 'jo*n', true)->toOpenSearchQuery());
    }
}
