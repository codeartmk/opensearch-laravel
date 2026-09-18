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
     * @throws OpenSearchCreateException When a bulk request reports errors. Earlier chunks stay indexed; the exception's getIndexedCount() says how many documents made it in.
     */
    public function createAll(?callable $callable = null, int $size = 100): bool
    {
        $query = $this->model::query();

        if ($callable instanceof \Closure) {
            $query = $callable($query);
        }

        $indexedCount = 0;

        $query->chunkById($size, function ($entities) use (&$indexedCount) {
            $indexedCount += $this->bulkIndex($entities, $indexedCount);
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
     * @throws OpenSearchCreateException When a bulk request reports errors. Earlier chunks stay indexed.
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

        $indexedCount = 0;

        foreach ($query->find($ids)->chunk($size) as $chunk) {
            $indexedCount += $this->bulkIndex($chunk, $indexedCount);
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
     * @param int $indexedBefore Documents indexed by earlier requests of the same run, reported if this one fails
     *
     * @return int The number of documents this request indexed
     * @throws OpenSearchCreateException When the response reports errors; earlier requests stay indexed
     */
    private function bulkIndex(iterable $entities, int $indexedBefore): int
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
        $indexed = $this->countSuccessfulItems($results);

        if (isset($results['errors']) && $results['errors'] === true) {
            throw new OpenSearchCreateException($this->indexName, $results, $indexedBefore + $indexed);
        }

        return $indexed;
    }

    /**
     * Counts the items of a bulk response that carry no error. A request can partially succeed.
     */
    private function countSuccessfulItems(array $results): int
    {
        $successful = 0;

        foreach ($results['items'] ?? [] as $item) {
            foreach ((array)$item as $result) {
                if (is_array($result) && !isset($result['error'])) {
                    $successful++;
                }
            }
        }

        return $successful;
    }
}
