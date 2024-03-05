<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Aggregations\Aggregation;
use Codeart\OpensearchLaravel\Aggregations\AggregationBuilder;
use Codeart\OpensearchLaravel\Search\Query;
use Codeart\OpensearchLaravel\Search\SearchBuilder;
use Codeart\OpensearchLaravel\Search\Sort;
use OpenSearch\Client;

class OpenSearchBuilder
{
    private SearchBuilder $searchBuilder;
    private AggregationBuilder $aggregationBuilder;
    private int $size = 10000;

    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchable $model
    )
    {
    }

    /**
     * @param array $parameters
     * @return $this
     * @throws \Exception
     */
    public function search(array $parameters): self
    {
        if (!count($parameters)) {
            throw new \Exception('Too few parameters to search method. At least Query required.');
        }

        if (count($parameters) > 2) {
            throw new \Exception('Too many parameters to search method.');
        }

        $this->searchBuilder = new SearchBuilder();

        foreach ($parameters as $parameter) {
            if ($parameter instanceof Sort) {
                $this->searchBuilder->setSort($parameter);
            }

            if ($parameter instanceof Query) {
                $this->searchBuilder->setQuery($parameter);
            }
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function aggregations(Aggregation|array $parameters): self
    {
        if (is_array($parameters) && !count($parameters)) {
            throw new \Exception('Too few parameters to aggregation method. At one required.');

        }

        $this->aggregationBuilder = new AggregationBuilder($parameters);

        return $this;
    }

    public function size(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function get(): array
    {
        $parameters = [
            "index" => $this->model->openSearchIndexName(),
            "size" => $this->size,
            "body" => [
                ...(isset($this->searchBuilder) ? $this->searchBuilder->toOpenSearchQuery() : []),
                ...(isset($this->aggregationBuilder) ? $this->aggregationBuilder->toOpenSearchQuery() : [])
            ],
        ];

        return $this->client->search($parameters);
    }
}
