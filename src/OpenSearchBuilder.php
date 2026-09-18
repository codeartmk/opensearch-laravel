<?php

namespace Codeart\OpensearchLaravel;

use Codeart\OpensearchLaravel\Aggregations\Aggregation;
use Codeart\OpensearchLaravel\Aggregations\AggregationBuilder;
use Codeart\OpensearchLaravel\Exceptions\InvalidAggregationParametersException;
use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Search\Query;
use Codeart\OpensearchLaravel\Search\SearchBuilder;
use Codeart\OpensearchLaravel\Search\Sort;
use OpenSearch\Client;

/**
 * Builds a search request against the model's index and sends it with get().
 *
 * search() sets the query and sort, aggregations() the aggregations; size(), from(), source(), highlight()
 * and trackTotalHits() shape the response and are only sent when called. Every setter replaces what an
 * earlier call set.
 */
class OpenSearchBuilder
{
    private SearchBuilder $searchBuilder;
    private AggregationBuilder $aggregationBuilder;
    private ?int $size = null;
    private ?int $from = null;
    private array|bool|string|null $source = null;
    private ?array $highlight = null;
    private bool|int|null $trackTotalHits = null;

    /**
     * @param Client $client The client the search is sent with
     * @param OpenSearchable $model The model whose index is searched
     */
    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchable $model
    )
    {
    }

    /**
     * Sets the query and the sort of the request, replacing an earlier search() call.
     *
     * Order doesn't matter. Nothing is wrapped implicitly: a bare BoolQuery or query type has to be
     * put in a Query first. The previous search is only replaced once the new one is valid.
     *
     * @param array<array-key, Query|Sort> $parameters One Query and/or one Sort, e.g. [Query::make([...]), Sort::make([...])]
     * @return $this
     * @throws InvalidSearchParametersException When the array is empty, has more than two items, has an item
     *                                          that is neither a Query nor a Sort, or has two of the same kind
     */
    public function search(array $parameters): self
    {
        if (!count($parameters)) {
            throw new InvalidSearchParametersException('Too few parameters to search method. At least Query required.');
        }

        if (count($parameters) > 2) {
            throw new InvalidSearchParametersException('Too many parameters to search method.');
        }

        $searchBuilder = new SearchBuilder();
        $hasQuery = false;
        $hasSort = false;

        foreach ($parameters as $parameter) {
            if ($parameter instanceof Query) {
                if ($hasQuery) {
                    throw new InvalidSearchParametersException('The search method accepts only one Query.');
                }

                $searchBuilder->setQuery($parameter);
                $hasQuery = true;

                continue;
            }

            if ($parameter instanceof Sort) {
                if ($hasSort) {
                    throw new InvalidSearchParametersException('The search method accepts only one Sort.');
                }

                $searchBuilder->setSort($parameter);
                $hasSort = true;

                continue;
            }

            throw new InvalidSearchParametersException(sprintf(
                'The search method accepts only Query and Sort instances, %s given.',
                get_debug_type($parameter)
            ));
        }

        // Only replace the previous search once the new parameters are known to be valid.
        $this->searchBuilder = $searchBuilder;

        return $this;
    }

    /**
     * Sets the aggregations of the request, sent under `aggs`, replacing an earlier aggregations() call.
     * Every level is built and validated here, so errors surface now rather than at get().
     *
     * @param Aggregation|array<array-key, Aggregation> $parameters One aggregation, or several siblings
     * @return $this
     * @throws InvalidAggregationParametersException When the list is empty, has an item that isn't an Aggregation,
     *                                               or has two aggregations with the same name at one level
     */
    public function aggregations(Aggregation|array $parameters): self
    {
        $this->aggregationBuilder = new AggregationBuilder($parameters);

        return $this;
    }

    /**
     * The number of hits to return. Only sent when called, so OpenSearch's default of 10 applies otherwise.
     * Call `size(0)` for aggregation-only searches.
     *
     * @param int $size The maximum number of hits to return
     * @return $this
     */
    public function size(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Skips the first `$from` hits. `from + size` can't exceed the index's max_result_window (10000 by default).
     *
     * @param int $from The number of hits to skip
     * @return $this
     */
    public function from(int $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Chooses which parts of each hit's `_source` are returned, sent as `_source`.
     *
     * @param string|list<string>|array{includes?: list<string>, excludes?: list<string>}|bool $source
     *        A field or list of fields to return (wildcards allowed), ['includes' => [...], 'excludes' => [...]],
     *        or false to leave out the source
     * @return $this
     */
    public function source(array|bool|string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Highlights the matches in the given fields, sent as `highlight`.
     *
     * The two forms of `$fields` can be mixed: a list item is a field name highlighted with the default options
     * (sent as `{}`), a string key is a field name mapped to its own options.
     *
     * @param array<int|string, string|array<string, mixed>> $fields Field names, or field name => highlight options,
     *                                                           e.g. ['title', 'body' => ['fragment_size' => 50]]
     * @param array<string, mixed> $options Top-level highlight options, e.g. ['pre_tags' => ['<em>'], 'post_tags' => ['</em>']].
     *                                      A `fields` key here is replaced by `$fields`.
     * @return $this
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
     * Controls how accurately `hits.total` is counted, sent as `track_total_hits`. Without it OpenSearch
     * counts accurately up to 10000.
     *
     * @param bool|int $trackTotalHits true to count every hit, false to skip counting, or the number to count up to
     * @return $this
     */
    public function trackTotalHits(bool|int $trackTotalHits = true): self
    {
        $this->trackTotalHits = $trackTotalHits;

        return $this;
    }

    /**
     * Sends the search and returns the raw response. Without search() and aggregations() the body is empty,
     * which matches every document.
     *
     * @return array<string, mixed> The OpenSearch response as is (`took`, `hits`, `aggregations`, ...); nothing is hydrated
     */
    public function get(): array
    {
        $parameters = [
            "index" => IndexNameResolver::resolve($this->model),
            "body" => [
                ...(isset($this->searchBuilder) ? $this->searchBuilder->toOpenSearchQuery() : []),
                ...(isset($this->aggregationBuilder) ? $this->aggregationBuilder->toOpenSearchQuery() : [])
            ],
        ];

        if (!is_null($this->size)) {
            $parameters["size"] = $this->size;
        }

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
