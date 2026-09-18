<?php

namespace Codeart\OpensearchLaravel\Search;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * The `sort` part of the request body. The parameters are passed through verbatim, in OpenSearch's own
 * sort syntax, and are not validated.
 *
 * @see https://opensearch.org/docs/latest/search-plugins/searching-data/sort/
 */
class Sort implements OpenSearchQuery
{
    /**
     * @param array<array-key, mixed> $parameters The sort list as OpenSearch expects it, e.g.
     *                                            [['created_at' => ['order' => 'desc']], '_score']
     */
    public function __construct(
        private readonly array $parameters
    ){}

    /**
     * @param array<array-key, mixed> $parameters The sort list as OpenSearch expects it, e.g.
     *                                            [['created_at' => ['order' => 'desc']], '_score']
     * @return self
     */
    public static function make(array $parameters): self
    {
        return new self($parameters);
    }


    /**
     * @return array{sort: array<array-key, mixed>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'sort' => [
                ...$this->parameters
            ]
        ];
    }
}
