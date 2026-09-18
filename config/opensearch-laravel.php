<?php

return [
    // The cluster URL, e.g. https://search.example.com:9200. Don't put credentials in it
    // (https://user:pass@host) — use username and password below instead.
    'host' => env('OPENSEARCH_HOST', 'http://localhost:9200'),

    // Basic-auth credentials. Leave the username unset for a cluster without the security
    // plugin: no auth header is sent then.
    'username' => env('OPENSEARCH_USERNAME'),
    'password' => env('OPENSEARCH_PASSWORD'),

    // TLS certificate verification, passed to Guzzle's "verify" option:
    // true verifies against the system CA bundle, a string is the path to a CA bundle file
    // (or a directory of certificates) for a private CA, and false turns verification off.
    // Only set false for a local cluster with a self-signed certificate — it accepts any
    // certificate, so the connection can be intercepted.
    'ssl_verification' => env('OPENSEARCH_SSL_VERIFICATION', true),

    'index_prefix' => env('OPENSEARCH_INDEX_PREFIX', ''),
];
