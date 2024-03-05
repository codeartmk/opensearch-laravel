<?php

namespace Codeart\OpensearchLaravel\Factories;

use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class OpensearchClientFactory
{
    public function createClient(): Client
    {
        return (new ClientBuilder())
            ->setHosts([config('opensearch-laravel.host')])
            ->setBasicAuthentication(config('opensearch-laravel.username'), config('opensearch-laravel.username'))
            ->setSSLVerification(config('opensearch-laravel.ssl_verification'))
            ->build();
    }
}