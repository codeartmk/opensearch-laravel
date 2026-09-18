<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use OpenSearch\Client;
use OpenSearch\Exception\NotFoundHttpException;
use OpenSearch\Exception\OpenSearchExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Reads the health of the cluster and of individual indices, for an application's own
 * health endpoints. It is not model-centric: indices are addressed by their full name.
 */
class OpenSearchHealth
{
    public function __construct(
        private readonly OpensearchClientFactory $clientFactory
    ){}

    /**
     * Whether the cluster answers a ping. Never throws: a connection failure, a timeout or
     * an HTTP error (e.g. 401 on wrong credentials) all return false.
     *
     * @return bool
     */
    public function isReachable(): bool
    {
        try {
            return $this->client()->ping();
        } catch (ClientExceptionInterface|OpenSearchExceptionInterface) {
            return false;
        }
    }

    /**
     * The raw `GET _cluster/health` response (`status`, `number_of_nodes`, `unassigned_shards`, ...).
     *
     * @return array<string, mixed>
     */
    public function cluster(): array
    {
        return $this->client()->cluster()->health();
    }

    /**
     * Health, size and the common settings of one index, as one flat array.
     *
     * `docs_count` counts primary documents only; `store_size_in_bytes` includes replicas.
     * A setting that is not set on the index (so the cluster default applies) is null.
     * The name is used as given — for a model's index, pass `IndexNameResolver::resolve($model)`
     * so the configured prefix is included.
     *
     * @param string $indexName
     * @return array{
     *     index: string,
     *     status: string,
     *     docs_count: int,
     *     store_size_in_bytes: int,
     *     number_of_shards: int|null,
     *     number_of_replicas: int|null,
     *     refresh_interval: string|null,
     *     max_result_window: int|null,
     * }
     * @throws NotFoundHttpException When the index does not exist.
     */
    public function index(string $indexName): array
    {
        // Settings are read first: on a missing index they fail with a 404 straight away,
        // whereas the index health call would wait for its 30s timeout before answering.
        $settingsResponse = $this->client()->indices()->getSettings(['index' => $indexName]);
        $health = $this->client()->cluster()->health(['index' => $indexName]);
        $stats = $this->client()->indices()->stats(['index' => $indexName]);

        // The response is keyed by the concrete index name, which differs from the given name for an alias.
        $indexSettings = $settingsResponse[$indexName] ?? reset($settingsResponse);
        $settings = $indexSettings['settings']['index'] ?? [];

        return [
            'index' => $indexName,
            'status' => $health['status'],
            'docs_count' => (int)($stats['_all']['primaries']['docs']['count'] ?? 0),
            'store_size_in_bytes' => (int)($stats['_all']['total']['store']['size_in_bytes'] ?? 0),
            'number_of_shards' => $this->intOrNull($settings['number_of_shards'] ?? null),
            'number_of_replicas' => $this->intOrNull($settings['number_of_replicas'] ?? null),
            'refresh_interval' => $settings['refresh_interval'] ?? null,
            'max_result_window' => $this->intOrNull($settings['max_result_window'] ?? null),
        ];
    }

    /**
     * One summary for a dashboard or health endpoint. Never throws when the cluster can't be
     * reached: `reachable` is then false and everything else is null. A requested index that
     * does not exist is null under its name.
     *
     * @param string[] $indexNames
     * @return array{
     *     reachable: bool,
     *     cluster: array<string, mixed>|null,
     *     version: string|null,
     *     indices: array<string, array<string, mixed>|null>,
     * }
     */
    public function report(array $indexNames = []): array
    {
        $unreachable = [
            'reachable' => false,
            'cluster' => null,
            'version' => null,
            'indices' => array_fill_keys($indexNames, null),
        ];

        if (!$this->isReachable()) {
            return $unreachable;
        }

        try {
            $indices = [];

            foreach ($indexNames as $indexName) {
                try {
                    $indices[$indexName] = $this->index($indexName);
                } catch (NotFoundHttpException) {
                    $indices[$indexName] = null;
                }
            }

            return [
                'reachable' => true,
                'cluster' => $this->cluster(),
                'version' => $this->client()->info()['version']['number'] ?? null,
                'indices' => $indices,
            ];
        } catch (ClientExceptionInterface) {
            // The connection dropped after the ping succeeded.
            return $unreachable;
        }
    }

    private function client(): Client
    {
        return $this->clientFactory->createClient();
    }

    private function intOrNull(mixed $value): ?int
    {
        return $value === null ? null : (int)$value;
    }
}
