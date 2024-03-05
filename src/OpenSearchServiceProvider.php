<?php

namespace Codeart\OpensearchLaravel;

use Illuminate\Support\ServiceProvider;

class OpenSearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/opensearch-laravel.php', 'opensearch-laravel');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/opensearch-laravel.php' => config_path('opensearch-laravel.php'),
        ],'config');

    }
}