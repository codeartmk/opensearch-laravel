<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Terms;
use PHPUnit\Framework\TestCase;

class TermsTest extends TestCase
{
    public function testBuildsATermsQueryFromAList()
    {
        $this->assertEquals([
            'terms' => [
                'tags' => ['fox', 'bread'],
            ],
        ], Terms::make('tags', ['fox', 'bread'])->toOpenSearchQuery());
    }

    public function testWrapsASingleValueInAList()
    {
        $this->assertEquals([
            'terms' => [
                'tags' => ['fox'],
            ],
        ], Terms::make('tags', 'fox')->toOpenSearchQuery());
    }
}
