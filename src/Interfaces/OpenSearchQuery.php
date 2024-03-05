<?php

namespace Codeart\OpensearchLaravel\Interfaces;

interface OpenSearchQuery
{
    public function toOpenSearchQuery(): array;
}
