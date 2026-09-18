<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Exceptions\ModelException;
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
        $this->indexName = IndexNameResolver::resolve($this->model);
    }

    /**
     * Indexes every model, one bulk request per chunk. The document `_id` is the model's primary key.
     *
     * The models are paged with chunkById() (keyset pagination on the primary key), so rows deleted or added
     * while the run is in progress don't shift the following chunks. It orders by the primary key, so an
     * orderBy() or a join added in the callback can conflict with the paging — the callback is meant for
     * eager loading, not for reordering.
     *
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

        $query->chunkById($size, function ($entities) {
            $this->bulkIndex($entities);
        });

        return true;
    }

    /**
     * Indexes the models with the given primary key(s). The document `_id` is the model's primary key.
     *
     * A single id goes through the `_create` endpoint, which refuses to overwrite an existing document.
     * An array of ids is sent as bulk `index` actions in chunks of `$size`, which overwrite existing documents.
     * The callback is meant for eager loading, not for reordering or joins.
     *
     * @param int|string|array $ids The primary key, or keys, of the models you want to create
     * @param callable|null $callable For eager loading relationships. Ex. fn($query) => $query->with('relationship')
     * @param int $size The chunk size for the bulk update ( default = 100 )
     *
     * @return bool
     * @throws ModelException When a single id is given and no model has it
     * @throws OpenSearchCreateException
     */
    public function create(int|string|array $ids, ?callable $callable = null, int $size = 100): bool
    {
        $query = $this->model::query();

        if ($callable instanceof \Closure) {
            $query = $callable($query);
        }

        if (!is_array($ids)) {
            $entity = $query->find($ids);

            if (is_null($entity)) {
                throw new ModelException("No model found with id:$ids for index:$this->indexName.");
            }

            $parameters = [
                'index' => $this->indexName,
                'id' => $entity->getKey(),
                'refresh' => true,
                'body' => $entity->openSearchArray(),
            ];

            $this->client->create($parameters);

            return true;
        }

        foreach ($query->find($ids)->chunk($size) as $chunk) {
            $this->bulkIndex($chunk);
        }

        return true;
    }

    /**
     * @param int|string $id The primary key of the model that needs to be created or updated
     * @param callable|null $callable For eager loading relationships. Ex. fn($query) => $query->with('relationship')
     *
     * @return array
     * @throws ModelException
     */
    public function createOrUpdate(int|string $id, ?callable $callable = null): array
    {
        $query = $this->model::query();

        if ($callable instanceof \Closure) {
            $query = $callable($query);
        }

        $entity = $query->find($id);

        if (is_null($entity)) {
            throw new ModelException("No model found with id:$id for index:$this->indexName.");
        }

        $parameters = [
            'index' => $this->indexName,
            'id' => $entity->getKey(),
            'refresh' => true,
            'retry_on_conflict' => 5,
            'body' => [
                'doc' => $entity->openSearchArray(),
                'doc_as_upsert' => true,
            ],
        ];

        return $this->client->update($parameters);
    }

    /**
     * @param int|string $id The primary key of the model whose document needs to be deleted
     *
     * @return array
     */
    public function delete(int|string $id): array
    {
        $parameters = [
            'index' => $this->indexName,
            'id' => $id,
        ];

        return $this->client->delete($parameters);
    }

    /**
     * Sends one bulk request with an `index` action per model.
     *
     * @param iterable $entities
     *
     * @throws OpenSearchCreateException
     */
    private function bulkIndex(iterable $entities): void
    {
        $bulk['body'] = [];

        foreach ($entities as $entity) {
            $bulk['body'][] = [
                'index' => [
                    '_index' => $this->indexName,
                    '_id' => $entity->getKey(),
                ],
            ];

            $bulk['body'][] = $entity->openSearchArray();
        }

        $results = $this->client->bulk($bulk);

        if (isset($results['errors']) && $results['errors'] === true) {
            throw new OpenSearchCreateException($this->indexName, $results);
        }
    }
}
