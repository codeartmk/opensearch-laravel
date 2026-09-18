<?php

namespace Codeart\OpensearchLaravel\Factories;

use OpenSearch\Client;
use OpenSearch\GuzzleClientFactory;

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
     */
    private function options(): array
    {
        $options = [
            'base_uri' => config('opensearch-laravel.host'),
            'verify' => config('opensearch-laravel.ssl_verification'),
        ];

        $username = config('opensearch-laravel.username');

        // Without a username Guzzle would still send an empty basic-auth header, which a cluster
        // with no security plugin doesn't expect.
        if ($username !== null && $username !== '') {
            $options['auth'] = [$username, config('opensearch-laravel.password')];
        }

        return $options;
    }
}
