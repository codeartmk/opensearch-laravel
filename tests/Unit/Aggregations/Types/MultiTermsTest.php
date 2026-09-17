<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Aggregations\Types;

use Codeart\OpensearchLaravel\Aggregations\Types\MultiTerms;
use PHPUnit\Framework\TestCase;

class MultiTermsTest extends TestCase
{
    public function testBuildsAMultiTermsAggregation()
    {
        $this->assertEquals([
            'multi_terms' => [
                'terms' => [
                    ['field' => 'category'],
                    ['field' => 'tags'],
                ],
                'size' => 5,
            ],
        ], MultiTerms::make(['category', 'tags'], 5)->toOpenSearchQuery());
    }
}
