<?php

namespace Codeart\OpensearchLaravel\Traits;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Codeart\OpensearchLaravel\OpenSearch;
use Illuminate\Support\Str;

/**
 * The default OpenSearchable implementation for an Eloquent model. Override the mapping, document body
 * or index name methods as needed; opensearch() itself is final.
 */
trait HasOpenSearchDocuments
{
    /**
     * The entry point. It builds on a new, empty instance of the model, so state on the calling
     * instance is not carried over: `$user->opensearch()` behaves like `User::opensearch()`.
     */
    final public static function opensearch(): OpenSearch
    {
        return new OpenSearch(new self(), app(OpensearchClientFactory::class));
    }

    /**
     * No mappings by default, so OpenSearch infers them.
     *
     * @return array<string, mixed>
     */
    public function openSearchMapping(): array
    {
        return [];
    }

    /**
     * The model's toArray(), so hidden attributes stay out and appended ones are included.
     *
     * @return array<string, mixed>
     */
    public function openSearchArray(): array
    {
        return $this->toArray();
    }

    /**
     * The lowercased plural of the class basename, e.g. `App\Models\User` → `users`.
     */
    public function openSearchIndexName(): string
    {
        return strtolower(Str::plural(basename(str_replace('\\', '/', $this::class))));
    }
}
