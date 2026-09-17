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
    private ?int $from = null;
    private array|bool|string|null $source = null;
    private ?array $highlight = null;
    private bool|int|null $trackTotalHits = null;

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

    /**
     * Skips the first `$from` hits. `from + size` can't exceed the index's max_result_window (10000 by default),
     * so lower `size()` when paginating.
     */
    public function from(int $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param array|bool|string $source A field or list of fields to return, ['includes' => [...], 'excludes' => [...]],
     *                                  or false to leave out the source
     */
    public function source(array|bool|string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @param array $fields Field names, or field name => highlight options, e.g. ['title', 'body' => ['fragment_size' => 50]]
     * @param array $options Top-level highlight options, e.g. ['pre_tags' => ['<em>'], 'post_tags' => ['</em>']]
     */
    public function highlight(array $fields, array $options = []): self
    {
        $highlightFields = [];

        foreach ($fields as $field => $fieldOptions) {
            if (is_int($field)) {
                $field = $fieldOptions;
                $fieldOptions = [];
            }

            // Fields without options have to be sent as JSON objects, not empty arrays.
            $highlightFields[$field] = $fieldOptions ?: new \stdClass();
        }

        $this->highlight = [
            ...$options,
            'fields' => $highlightFields,
        ];

        return $this;
    }

    /**
     * @param bool|int $trackTotalHits true to count every hit, false to skip counting, or the number to count up to
     */
    public function trackTotalHits(bool|int $trackTotalHits = true): self
    {
        $this->trackTotalHits = $trackTotalHits;

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

        if (!is_null($this->from)) {
            $parameters["from"] = $this->from;
        }

        if (!is_null($this->source)) {
            $parameters["body"]["_source"] = $this->source;
        }

        if (!is_null($this->highlight)) {
            $parameters["body"]["highlight"] = $this->highlight;
        }

        if (!is_null($this->trackTotalHits)) {
            $parameters["body"]["track_total_hits"] = $this->trackTotalHits;
        }

        return $this->client->search($parameters);
    }
}
