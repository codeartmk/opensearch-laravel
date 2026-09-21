<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Exceptions\IndexAlreadyExistException;
use Codeart\OpensearchLaravel\Exceptions\InvalidIndexNameException;
use OpenSearch\Client;

/**
 * Creates, deletes and checks the model's index. The name is the configured prefix followed by the
 * model's openSearchIndexName(), see IndexNameResolver.
 */
class OpenSearchIndices
{
    private string $indexName;

    /**
     * @param Client $client The client the requests are sent with
     * @param OpenSearchable $model The model whose index is managed; its openSearchMapping() is used on create
     */
    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchable $model
    ){
        $this->indexName = IndexNameResolver::resolve($this->model);
    }

    /**
     * Creates the index for the model
     *
     * $configuration['number_of_shards']   = (int) The number of shards (default = 1)
     * $configuration['number_of_replicas'] = (int) The number of replicas (default = 1)
     * $configuration['refresh_interval']   = (string) The refresh interval (default = 1s)
     *
     * Other keys are ignored. The model's openSearchMapping() is sent as `mappings` when it isn't empty.
     *
     * @param array{number_of_shards?: int, number_of_replicas?: int, refresh_interval?: string} $configuration Associative array of parameters
     * @return array<string, mixed> The raw create-index response
     * @throws IndexAlreadyExistException When the index already exists, checked with exists() first
     */
    public function create(array $configuration = []): array
    {
        if($this->exists()) {
            throw new IndexAlreadyExistException($this->indexName);
        }

        $body = [
            'settings' => [
                'number_of_shards'   => $configuration['number_of_shards'] ?? 1,
                'number_of_replicas' => $configuration['number_of_replicas'] ?? 1,
                'refresh_interval'   => $configuration['refresh_interval'] ?? '1s',
            ],
        ];

        $mappings = $this->model->openSearchMapping();

        if(count($mappings)) {
            $body['mappings'] = $mappings;
        }

        $parameters = [
            "index" => $this->indexName,
            "body"  => $body,
        ];

        return $this->client->indices()->create($parameters);
    }

    /**
     * Deletes the index for the model
     *
     * OpenSearch expands a wildcard, a comma-separated list or `_all` in the name to every matching index,
     * and deleting by a pattern is allowed by default (`action.destructive_requires_name` is false). A name
     * like that is refused here, since it can never be a single index: index names can't contain `*` or `,`.
     *
     * @return array<string, mixed> The raw delete-index response
     * @throws InvalidIndexNameException When the resolved index name can match more than one index
     */
    public function delete(): array
    {
        if (!IndexNameResolver::isConcrete($this->indexName)) {
            throw new InvalidIndexNameException($this->indexName, 'Deleting an index');
        }

        $parameters = [
            'index' => $this->indexName
        ];

        return $this->client->indices()->delete($parameters);
    }

    /**
     * Check if the index for the model exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        $parameters = [
            'index' => $this->indexName
        ];

        return $this->client->indices()->exists($parameters);
    }
}