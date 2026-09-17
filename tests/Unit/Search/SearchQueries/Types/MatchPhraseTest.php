<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchPhrase;
use PHPUnit\Framework\TestCase;

class MatchPhraseTest extends TestCase
{
    public function testBuildsAMatchPhraseQuery()
    {
        $this->assertEquals([
            'match_phrase' => [
                'title' => 'quick brown',
            ],
        ], MatchPhrase::make('title', 'quick brown')->toOpenSearchQuery());
    }

    public function testBuildsAMatchPhraseQueryWithSlop()
    {
        $this->assertEquals([
            'match_phrase' => [
                'title' => [
                    'query' => 'quick fox',
                    'slop' => 2,
                ],
            ],
        ], MatchPhrase::make('title', 'quick fox', 2)->toOpenSearchQuery());
    }
}
