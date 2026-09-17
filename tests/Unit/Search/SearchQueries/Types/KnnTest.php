<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Knn;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term;
use PHPUnit\Framework\TestCase;

class KnnTest extends TestCase
{
    public function testBuildsAKnnQuery()
    {
        $this->assertEquals([
            'knn' => [
                'embedding' => [
                    'vector' => [0.1, 0.2, 0.3],
                    'k' => 5,
                ],
            ],
        ], Knn::make('embedding', [0.1, 0.2, 0.3], 5)->toOpenSearchQuery());
    }

    public function testBuildsAKnnQueryWithAFilter()
    {
        $this->assertEquals([
            'knn' => [
                'embedding' => [
                    'vector' => [0.1, 0.2, 0.3],
                    'k' => 5,
                    'filter' => ['term' => ['category' => 'food']],
                ],
            ],
        ], Knn::make('embedding', [0.1, 0.2, 0.3], 5, Term::make('category', 'food'))->toOpenSearchQuery());
    }
}
