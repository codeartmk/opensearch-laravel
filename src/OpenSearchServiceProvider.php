<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Illuminate\Support\ServiceProvider;

class OpenSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/opensearch-laravel.php', 'opensearch-laravel');

        $this->app->singleton(OpensearchClientFactory::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/opensearch-laravel.php' => config_path('opensearch-laravel.php'),
        ], 'config');
    }
}