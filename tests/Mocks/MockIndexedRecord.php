<?php

namespace Codeart\OpensearchLaravel\Tests\Mocks;

use Codeart\OpensearchLaravel\OpenSearchable;
use Codeart\OpensearchLaravel\Traits\HasOpenSearchDocuments;
use Illuminate\Database\Eloquent\Model;

/**
 * A real, table-backed model for the tests that need Eloquent to hit a database.
 */
class MockIndexedRecord extends Model implements OpenSearchable
{
    use HasOpenSearchDocuments;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
