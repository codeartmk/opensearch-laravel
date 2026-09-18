<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchPhrasePrefix;
use PHPUnit\Framework\TestCase;

class MatchPhrasePrefixTest extends TestCase
{
    public function testBuildsAMatchPhrasePrefixQuery()
    {
        $this->assertEquals([
            'match_phrase_prefix' => [
                'title' => 'the wind ri',
            ],
        ], MatchPhrasePrefix::make('title', 'the wind ri')->toOpenSearchQuery());
    }

    public function testBuildsAMatchPhrasePrefixQueryWithAnOptionsObject()
    {
        $this->assertEquals([
            'match_phrase_prefix' => [
                'title' => [
                    'query' => 'the wind ri',
                    'max_expansions' => 10,
                ],
            ],
        ], MatchPhrasePrefix::make('title', ['query' => 'the wind ri', 'max_expansions' => 10])->toOpenSearchQuery());
    }
}
