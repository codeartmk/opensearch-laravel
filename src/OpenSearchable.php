<?php

namespace Codeart\OpensearchLaravel;

interface OpenSearchable
{
    public static function opensearch(): OpenSearch;

    public function openSearchMapping(): array;

    public function openSearchArray(): array;

    public function openSearchIndexName(): string;
}
