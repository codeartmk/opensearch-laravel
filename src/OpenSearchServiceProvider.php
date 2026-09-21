<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Merges and publishes the package config and binds the client factory as a singleton, so every
 * opensearch() call in a request shares one client.
 */
class OpenSearchServiceProvider extends ServiceProvider
{
    /**
     * Merges the package config under `opensearch-laravel` and registers the OpensearchClientFactory singleton.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/opensearch-laravel.php', 'opensearch-laravel');

        $this->app->singleton(OpensearchClientFactory::class);
    }

    /**
     * Makes the config publishable with the `config` tag.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/opensearch-laravel.php' => config_path('opensearch-laravel.php'),
        ], 'config');
    }
}