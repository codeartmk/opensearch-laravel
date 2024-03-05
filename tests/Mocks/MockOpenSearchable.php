<?php

namespace Codeart\OpensearchLaravel\Tests\Mocks;

use Codeart\OpensearchLaravel\OpenSearch;
use Codeart\OpensearchLaravel\OpenSearchable;
use Codeart\OpensearchLaravel\Traits\HasOpenSearchDocuments;
use Illuminate\Database\Eloquent\Model;

class MockOpenSearchable extends Model implements OpenSearchable
{
    use HasOpenSearchDocuments;

    public int $id = 1;
}