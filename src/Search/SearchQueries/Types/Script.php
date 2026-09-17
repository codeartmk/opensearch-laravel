<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class Script implements SearchQueryType, OpenSearchQuery
{
    public function __construct(
        private readonly string $source,
        private readonly ?array $params,
        private readonly ?string $lang
    ){}

    public static function make(string $source, ?array $params = null, ?string $lang = null): self
    {
        return new self($source, $params, $lang);
    }

    public function toOpenSearchQuery(): array
    {
        $script = [
            'source' => $this->source
        ];

        if (!empty($this->params)) {
            $script['params'] = $this->params;
        }

        if (!is_null($this->lang)) {
            $script['lang'] = $this->lang;
        }

        return [
            'script' => [
                'script' => $script
            ]
        ];
    }
}
