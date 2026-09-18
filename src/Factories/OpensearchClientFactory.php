<?php

namespace Codeart\OpensearchLaravel\Factories;

use OpenSearch\Client;
use OpenSearch\GuzzleClientFactory;

/**
 * The only place a Client is built, and the only class that reads the connection config (the index
 * prefix is read by IndexNameResolver). Bound as a singleton, so swapping it in the container
 * changes what every Model::opensearch() call talks to.
 */
class OpensearchClientFactory
{
    private ?Client $client = null;

    /**
     * Returns the client, building it with GuzzleClientFactory from the config on first use and reusing it afterwards.
     */
    public function createClient(): Client
    {
        return $this->client ??= (new GuzzleClientFactory())->create($this->options());
    }

    /**
     * Drops the reused client so the next call builds a new one, e.g. after changing the config at runtime.
     */
    public function forgetClient(): void
    {
        $this->client = null;
    }

    /**
     * The Guzzle request options the client is built with.
     *
     * @return array{base_uri: mixed, verify: mixed, auth?: array{0: string, 1: string}}
     */
    private function options(): array
    {
        $options = [
            'base_uri' => config('opensearch-laravel.host'),
            // Passed through as-is: Guzzle takes true, false or the path to a CA bundle, so it must not be cast to bool.
            'verify' => config('opensearch-laravel.ssl_verification'),
        ];

        $username = config('opensearch-laravel.username');

        // Without a username Guzzle would still send an empty basic-auth header, which a cluster
        // with no security plugin doesn't expect.
        if ($username !== null && $username !== '') {
            // Guzzle 8 rejects a null password, and the password no longer has a default.
            $options['auth'] = [(string)$username, (string)config('opensearch-laravel.password')];
        }

        return $options;
    }
}
