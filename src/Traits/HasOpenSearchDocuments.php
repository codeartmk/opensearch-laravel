<?php

namespace Codeart\OpensearchLaravel\Traits;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Codeart\OpensearchLaravel\OpenSearch;
use Illuminate\Support\Str;

trait HasOpenSearchDocuments
{
    final public static function opensearch(): OpenSearch
    {
        return new OpenSearch(new self(), new OpensearchClientFactory());
    }

    public function openSearchMapping(): array
    {
        return [];
    }

    public function openSearchArray(): array
    {
        return $this->toArray();
    }

    public function openSearchIndexName(): string
    {
        return strtolower(Str::plural(basename(str_replace('\\', '/', $this::class))));
    }
}
