<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Exceptions\OpenSearchCreateException;
use OpenSearch\Client;

class OpenSearchDocuments
{
    private string $indexName;

    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchable $model
    )
    {
        $this->indexName = $this->model->openSearchIndexName();
    }

    /**
     * @param callable|null $callable For eager loading relationships. Ex. fn($query) => $query->with('relationship')
     * @param int $size The size of the chunks when indexing models ( default = 100 )
     *
     * @return bool
     * @throws OpenSearchCreateException
     */
    public function createAll(?callable $callable = null, int $size = 100): bool
    {
        $query = $this->model::query();

        if ($callable instanceof \Closure) {
            $query = $callable($query);
        }

        $query->chunk($size, function ($entities) {
            $bulk['body'] = [];

            foreach ($entities as $entity) {
                $bulk['body'][] = [
                    "index" => [
                        "_index" => $this->indexName,
                        "_id"    => $entity->id,
                    ],
                ];

                $bulk['body'][] = $entity->openSearchArray();
            }

            $results = $this->client->bulk($bulk);

            if (isset($results['errors']) && $results['errors'] === true) {
                throw new OpenSearchCreateException($this->indexName, $results);
            }
        });

        return true;
    }

    /**
     * @param int|array $ids The id's of the models you want to create
     * @param int $size The chunk size for the bulk update ( default = 100 )
     * @param callable|null $callable For eager loading relationships. Ex. fn($query) => $query->with('relationship')
     *
     * @return bool
     * @throws OpenSearchCreateException
     */
    public function create(int|array $ids, ?callable $callable = null, int $size = 100): bool
    {
        $query = $this->model::query();

        if ($callable instanceof \Closure) {
            $query = $callable($query);
        }

        $entities = $query->find($ids);

        if ($entities instanceof $this->model) {
            $parameters = [
                "index" => $this->indexName,
                "id" => $entities->id,
                "refresh" => true,
                "retry_on_conflict" => 5,
                "body" => [
                    "doc" => $entities->openSearchArray(),
                ],
            ];

            $this->client->create($parameters);

            return true;
        }

        foreach ($entities->chunk($size) as $chunk) {
            $bulk['body'] = [];

            foreach ($chunk as $entity) {
                $bulk['body'][] = [
                    "index" => [
                        "_index" => $this->indexName,
                        "_id" => $entity->id,
                    ],
                ];

                $bulk['body'][] = $entity->openSearchArray();
            }

            $results = $this->client->bulk($bulk);

            if (isset($results['errors']) && $results['errors'] === true) {
                throw new OpenSearchCreateException($this->indexName, $results);
            }
        }

        return true;
    }

    /**
     * @param int $id The id of the model that needs to be created or updated
     * @param callable|null $callable For eager loading relationships. Ex. fn($query) => $query->with('relationship')
     *
     * @return array
     */
    public function createOrUpdate(int $id, ?callable $callable = null): array
    {
        $query = $this->model::query();

        if ($callable instanceof \Closure) {
            $query = $callable($query);
        }

        $entity = $query->find($id);

        $parameters = [
            "index" => $this->indexName,
            "id" => $entity->id,
            "refresh" => true,
            "retry_on_conflict" => 5,
            "body" => [
                "doc" => $entity->openSearchArray(),
                'doc_as_upsert' => true
            ],
        ];

        return $this->client->update($parameters);
    }

    /**
     * @param int $id The id of the model that needs to be deleted
     *
     * @return array
     */
    public function delete(int $id): array
    {
        $parameters = [
            'index' => $this->indexName,
            'id' => $id
        ];

        return $this->client->delete($parameters);
    }
}