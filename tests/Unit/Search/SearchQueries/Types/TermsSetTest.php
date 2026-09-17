<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\TermsSet;
use PHPUnit\Framework\TestCase;

class TermsSetTest extends TestCase
{
    public function testBuildsATermsSetQueryWithAMinimumShouldMatchField()
    {
        $this->assertEquals([
            'terms_set' => [
                'tags' => [
                    'terms' => ['bread', 'eggs'],
                    'minimum_should_match_field' => 'required_matches',
                ],
            ],
        ], TermsSet::make('tags', ['bread', 'eggs'], 'required_matches')->toOpenSearchQuery());
    }

    public function testBuildsATermsSetQueryWithAMinimumShouldMatchScript()
    {
        $query = TermsSet::make('tags', ['bread', 'eggs'], minimumShouldMatchScript: 'Math.min(params.num_terms, 2)');

        $this->assertEquals([
            'terms_set' => [
                'tags' => [
                    'terms' => ['bread', 'eggs'],
                    'minimum_should_match_script' => [
                        'source' => 'Math.min(params.num_terms, 2)',
                    ],
                ],
            ],
        ], $query->toOpenSearchQuery());
    }
}
