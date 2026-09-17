<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\MoreLikeThis;
use PHPUnit\Framework\TestCase;

class MoreLikeThisTest extends TestCase
{
    public function testBuildsAMoreLikeThisQuery()
    {
        $this->assertEquals([
            'more_like_this' => [
                'fields' => ['title', 'body'],
                'like' => 'quick brown fox',
            ],
        ], MoreLikeThis::make(['title', 'body'], 'quick brown fox')->toOpenSearchQuery());
    }

    public function testBuildsAMoreLikeThisQueryWithDocumentsAndTuning()
    {
        $like = [['_index' => 'posts', '_id' => '1'], 'bread'];

        $this->assertEquals([
            'more_like_this' => [
                'fields' => ['title'],
                'like' => $like,
                'min_term_freq' => 1,
                'max_query_terms' => 12,
                'min_doc_freq' => 1,
            ],
        ], MoreLikeThis::make(['title'], $like, 1, 12, 1)->toOpenSearchQuery());
    }
}
