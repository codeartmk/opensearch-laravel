<?php

namespace Codeart\OpensearchLaravel;

/**
 * Implemented by an Eloquent model that is stored in OpenSearch. The HasOpenSearchDocuments trait
 * provides a default for every method.
 */
interface OpenSearchable
{
    /**
     * The entry point: `User::opensearch()->builder()`, `->indices()` or `->documents()`.
     */
    public static function opensearch(): OpenSearch;

    /**
     * The index mappings, sent as `mappings` when the index is created. An empty array leaves them out,
     * so OpenSearch infers the mapping from the first documents.
     *
     * @return array<string, mixed> e.g. ['properties' => ['name' => ['type' => 'text']]]
     */
    public function openSearchMapping(): array;

    /**
     * The document body stored for this model.
     *
     * @return array<string, mixed>
     */
    public function openSearchArray(): array;

    /**
     * The index name without the configured prefix; IndexNameResolver::resolve() prepends it.
     */
    public function openSearchIndexName(): string;
}
