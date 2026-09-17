<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\BoolQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Must;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Nested;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class NestedTest extends TestCase
{
    public function testBuildsANestedQuery()
    {
        $this->assertEquals([
            'nested' => [
                'path' => 'comments',
                'query' => [
                    'term' => ['comments.author' => 'ana'],
                ],
            ],
        ], Nested::make('comments', Term::make('comments.author', 'ana'))->toOpenSearchQuery());
    }

    public function testBuildsANestedQueryWithABoolQueryScoreModeAndInnerHits()
    {
        $query = Nested::make(
            'comments',
            BoolQuery::make([Must::make(Term::make('comments.author', 'ana'))]),
            'max',
            ['size' => 2]
        );

        $this->assertEquals([
            'nested' => [
                'path' => 'comments',
                'query' => [
                    'bool' => [
                        'must' => ['term' => ['comments.author' => 'ana']],
                    ],
                ],
                'score_mode' => 'max',
                'inner_hits' => ['size' => 2],
            ],
        ], $query->toOpenSearchQuery());
    }

    public function testSendsEmptyInnerHitsAsAnObject()
    {
        $json = json_encode(Nested::make('comments', Term::make('comments.author', 'ana'), innerHits: [])->toOpenSearchQuery());

        $this->assertStringContainsString('"inner_hits":{}', $json);
    }
}
