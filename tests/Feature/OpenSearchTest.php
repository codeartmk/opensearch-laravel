<?php

namespace Codeart\OpensearchLaravel\Tests\Feature;

use Codeart\OpensearchLaravel\OpenSearch;
use Codeart\OpensearchLaravel\OpenSearchBuilder;
use Codeart\OpensearchLaravel\OpenSearchDocuments;
use Codeart\OpensearchLaravel\OpenSearchIndices;
use Codeart\OpensearchLaravel\Tests\Mocks\MockOpenSearchable;
use Codeart\OpensearchLaravel\Tests\TestCase;

class OpenSearchTest extends TestCase
{
    public function testCanInitializeOpensearchFromAnOpensearchable()
    {
        $this->assertTrue(MockOpenSearchable::opensearch() instanceof OpenSearch);
        $this->assertTrue(MockOpenSearchable::opensearch()->indices() instanceof OpenSearchIndices);
        $this->assertTrue(MockOpenSearchable::opensearch()->documents() instanceof OpenSearchDocuments);
        $this->assertTrue(MockOpenSearchable::opensearch()->builder() instanceof OpenSearchBuilder);
    }
}