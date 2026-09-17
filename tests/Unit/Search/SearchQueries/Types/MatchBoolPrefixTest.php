<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchBoolPrefix;
use PHPUnit\Framework\TestCase;

class MatchBoolPrefixTest extends TestCase
{
    public function testBuildsAMatchBoolPrefixQuery()
    {
        $this->assertEquals([
            'match_bool_prefix' => [
                'title' => 'quick bro',
            ],
        ], MatchBoolPrefix::make('title', 'quick bro')->toOpenSearchQuery());
    }
}
