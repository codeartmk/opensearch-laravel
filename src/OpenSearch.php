<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use OpenSearch\Client;

class OpenSearch
{
    private Client $client;
    private OpenSearchable $model;
    public function __construct(OpenSearchable $model, OpensearchClientFactory $clientFactory)
    {
        $this->model = $model;
        $this->client = $clientFactory->createClient();
    }

    public function builder(): OpenSearchBuilder
    {
        return new OpenSearchBuilder($this->client, $this->model);
    }

    public function indices(): OpenSearchIndices
    {
        return new OpenSearchIndices($this->client, $this->model);
    }

    public function documents(): OpenSearchDocuments
    {
        return new OpenSearchDocuments($this->client, $this->model);
    }
}