<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class TermTest extends TestCase
{
    public function testBuildsATermQuery()
    {
        $this->assertSame([
            'term' => [
                'status' => 'active',
            ],
        ], Term::make('status', 'active')->toOpenSearchQuery());
    }

    public function testKeepsIntegerAndBooleanValuesAsTheirOwnTypes()
    {
        $this->assertSame(['term' => ['age' => 30]], Term::make('age', 30)->toOpenSearchQuery());
        $this->assertSame(['term' => ['verified' => true]], Term::make('verified', true)->toOpenSearchQuery());
    }
}
