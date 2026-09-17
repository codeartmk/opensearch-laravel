<?php

namespace Codeart\OpensearchLaravel\Factories;

use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class OpensearchClientFactory
{
    private ?Client $client = null;

    /**
     * Returns the client, building it from the config on first use and reusing it afterwards.
     */
    public function createClient(): Client
    {
        return $this->client ??= (new ClientBuilder())
            ->setHosts([config('opensearch-laravel.host')])
            ->setBasicAuthentication(config('opensearch-laravel.username'), config('opensearch-laravel.password'))
            ->setSSLVerification(config('opensearch-laravel.ssl_verification'))
            ->build();
    }

    /**
     * Drops the reused client so the next call builds a new one, e.g. after changing the config at runtime.
     */
    public function forgetClient(): void
    {
        $this->client = null;
    }
}
