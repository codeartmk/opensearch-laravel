<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchAll;
use PHPUnit\Framework\TestCase;

class MatchAllTest extends TestCase
{
    public function testBuildsAMatchAllQueryWithAnEmptyObject()
    {
        $this->assertSame('{"match_all":{}}', json_encode(MatchAll::make()->toOpenSearchQuery()));
    }
}
