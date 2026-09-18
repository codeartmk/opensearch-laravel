<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `script` query: matches documents for which the script returns true.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/specialized/script/
 */
class Script implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $source The script source, e.g. "doc['likes'].value > params.min"
     * @param array<string, mixed>|null $params Values the script reads from `params`. Null or an empty array leaves it
     *                                          out
     * @param string|null $lang The script language. Null leaves it out so OpenSearch's default (painless) applies
     */
    public function __construct(
        private readonly string $source,
        private readonly ?array $params,
        private readonly ?string $lang
    ){}

    /**
     * @param string $source The script source, e.g. "doc['likes'].value > params.min"
     * @param array<string, mixed>|null $params Values the script reads from `params`. Null or an empty array leaves it
     *                                          out
     * @param string|null $lang The script language. Null leaves it out so OpenSearch's default (painless) applies
     * @return self
     */
    public static function make(string $source, ?array $params = null, ?string $lang = null): self
    {
        return new self($source, $params, $lang);
    }

    /**
     * @return array{script: array{script: array{source: string, params?: array<string, mixed>, lang?: string}}}
     */
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
