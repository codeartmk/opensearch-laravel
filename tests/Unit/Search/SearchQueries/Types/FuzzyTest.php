<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Fuzzy;
use PHPUnit\Framework\TestCase;

class FuzzyTest extends TestCase
{
    public function testBuildsAFuzzyQueryWithTheDefaults()
    {
        $this->assertSame([
            'fuzzy' => [
                'name' => [
                    'value' => 'jonh',
                    'fuzziness' => 'AUTO',
                    'max_expansions' => 50,
                    'prefix_length' => 0,
                    'transpositions' => true,
                ],
            ],
        ], Fuzzy::make('name', 'jonh')->toOpenSearchQuery());
    }

    public function testBuildsAFuzzyQueryWithEveryOption()
    {
        $this->assertSame([
            'fuzzy' => [
                'name' => [
                    'value' => 'jonh',
                    'fuzziness' => 2,
                    'max_expansions' => 10,
                    'prefix_length' => 1,
                    'transpositions' => false,
                ],
            ],
        ], Fuzzy::make('name', 'jonh', 2, 10, 1, false)->toOpenSearchQuery());
    }
}
