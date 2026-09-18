<?php

namespace Codeart\OpensearchLaravel;

use Closure;
use Codeart\OpensearchLaravel\Exceptions\InvalidIndexNameException;
use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use OpenSearch\Client;
use OpenSearch\Exception\HttpExceptionInterface;
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
     * @throws InvalidIndexNameException When the name is empty, contains a wildcard or a comma, or is `_all`:
     *                                    OpenSearch would answer with the stats of several indices.
     * @throws NotFoundHttpException When the index does not exist.
     */
    public function index(string $indexName): array
    {
        if (!IndexNameResolver::isConcrete($indexName)) {
            throw new InvalidIndexNameException($indexName, 'An index health check');
        }

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
     * reached: `reachable` is then false and everything else is null.
     *
     * When the cluster answers but refuses or fails one of the calls with an HTTP error (a 403
     * because the user lacks the monitor privileges, a 5xx, ...), that part is null and the rest
     * is still reported: `cluster` or `version` is null, and a requested index is null under its
     * name — as it is when the index does not exist. Errors that point to a bug or a
     * misconfiguration in the application, such as an invalid Guzzle option, still throw.
     *
     * The cluster health response includes the cluster name and node and shard counts, and the
     * indices are listed by name, so don't return the whole report from a public route.
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
                $indices[$indexName] = $this->nullOnHttpError(fn() => $this->index($indexName));
            }

            return [
                'reachable' => true,
                'cluster' => $this->nullOnHttpError(fn() => $this->cluster()),
                'version' => $this->nullOnHttpError(fn() => $this->client()->info()['version']['number'] ?? null),
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

    /**
     * Runs the call and returns null when the cluster answers it with an HTTP error, e.g. a 404
     * for a missing index or a 403 without the monitor privileges.
     */
    private function nullOnHttpError(Closure $call): mixed
    {
        try {
            return $call();
        } catch (HttpExceptionInterface) {
            return null;
        }
    }

    private function intOrNull(mixed $value): ?int
    {
        return $value === null ? null : (int)$value;
    }
}
