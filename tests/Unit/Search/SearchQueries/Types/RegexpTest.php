<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Regexp;
use PHPUnit\Framework\TestCase;

class RegexpTest extends TestCase
{
    public function testBuildsARegexpQuery()
    {
        $this->assertSame([
            'regexp' => [
                'name' => [
                    'value' => 'jo.*n',
                    'case_insensitive' => false,
                ],
            ],
        ], Regexp::make('name', 'jo.*n')->toOpenSearchQuery());
    }

    public function testBuildsARegexpQueryCaseInsensitive()
    {
        $this->assertSame([
            'regexp' => [
                'name' => [
                    'value' => 'jo.*n',
                    'case_insensitive' => true,
                ],
            ],
        ], Regexp::make('name', 'jo.*n', true)->toOpenSearchQuery());
    }
}
