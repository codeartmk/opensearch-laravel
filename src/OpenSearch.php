<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use OpenSearch\Client;

/**
 * The entry object a searchable model returns from opensearch(). It holds the model and the shared client,
 * and hands out a fresh builder, indices or documents object for that model on every call.
 */
class OpenSearch
{
    private Client $client;
    private OpenSearchable $model;
    /**
     * @param OpenSearchable $model A throwaway instance of the model, used only for its index name, mapping and query
     * @param OpensearchClientFactory $clientFactory The container's factory; its cached client is shared by every call
     */
    public function __construct(OpenSearchable $model, OpensearchClientFactory $clientFactory)
    {
        $this->model = $model;
        $this->client = $clientFactory->createClient();
    }

    /**
     * Starts a new search against the model's index.
     */
    public function builder(): OpenSearchBuilder
    {
        return new OpenSearchBuilder($this->client, $this->model);
    }

    /**
     * Creates, deletes or checks the model's index.
     */
    public function indices(): OpenSearchIndices
    {
        return new OpenSearchIndices($this->client, $this->model);
    }

    /**
     * Writes the model's rows to its index, or deletes documents from it.
     */
    public function documents(): OpenSearchDocuments
    {
        return new OpenSearchDocuments($this->client, $this->model);
    }
}