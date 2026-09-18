<?php

namespace Codeart\OpensearchLaravel;

class IndexNameResolver
{
    /**
     * Resolves the name of the index a model is stored in: the configured
     * `opensearch-laravel.index_prefix` followed by the model's openSearchIndexName().
     *
     * The prefix is prepended verbatim, no separator is added (`local_` + `users` → `local_users`).
     * It is not validated here, so it must follow OpenSearch's index name rules itself:
     * lowercase, must not start with `_`, `-` or `+`, and must not contain spaces or
     * any of `\ / * ? " < > | , #`. OpenSearch rejects the request otherwise.
     *
     * @param OpenSearchable $model
     * @return string
     */
    public static function resolve(OpenSearchable $model): string
    {
        return (string)config('opensearch-laravel.index_prefix', '') . $model->openSearchIndexName();
    }

    /**
     * Whether the name can only address one index. OpenSearch expands a wildcard, a comma-separated
     * list or `_all` to every matching index, and an empty name to all of them on most endpoints.
     * None of those can be a real index name, so a name that fails this is never a single index.
     *
     * @param string $indexName
     * @return bool
     */
    public static function isConcrete(string $indexName): bool
    {
        return $indexName !== '' && $indexName !== '_all' && strpbrk($indexName, '*,') === false;
    }
}
