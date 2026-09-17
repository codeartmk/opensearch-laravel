<?php

namespace Codeart\OpensearchLaravel\Tests\Unit\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Search\SearchQueries\Types\Script;
use PHPUnit\Framework\TestCase;

class ScriptTest extends TestCase
{
    public function testBuildsAScriptQuery()
    {
        $this->assertEquals([
            'script' => [
                'script' => [
                    'source' => "doc['quantity'].value > 5",
                ],
            ],
        ], Script::make("doc['quantity'].value > 5")->toOpenSearchQuery());
    }

    public function testBuildsAScriptQueryWithParamsAndLanguage()
    {
        $this->assertEquals([
            'script' => [
                'script' => [
                    'source' => "doc['quantity'].value > params.min",
                    'params' => ['min' => 5],
                    'lang' => 'painless',
                ],
            ],
        ], Script::make("doc['quantity'].value > params.min", ['min' => 5], 'painless')->toOpenSearchQuery());
    }

    public function testLeavesOutEmptyParams()
    {
        $this->assertEquals([
            'script' => [
                'script' => [
                    'source' => 'true',
                ],
            ],
        ], Script::make('true', [])->toOpenSearchQuery());
    }
}
