<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Exceptions\IndexAlreadyExistException;
use OpenSearch\Client;

class OpenSearchIndices
{
    private string $indexName;

    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchable $model
    ){
        $this->indexName = $this->model->openSearchIndexName();
    }

    /**
     * Creates the index for the model
     *
     * $configuration['numberOfShards']   = (int) The number of shards (default = 1)
     * $configuration['numberOfReplicas'] = (int) The number of replicas (default = 1)
     * $configuration['refreshInterval']  = (string) The refresh interval (default = 1s)
     *
     * @param array $configuration Associative array of parameters
     * @return array
     * @throws IndexAlreadyExistException
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
     * @return array
     */
    public function delete(): array
    {
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