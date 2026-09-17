# Changelog

All notable changes to `codeartmk/opensearch-laravel` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-09-17

### Added

- Laravel 10, 11, 12 and 13 are now officially supported. A GitHub Actions workflow runs the test suite against each of them on every PHP version that Laravel release supports, with both the lowest and the newest allowed dependencies.
- `Aggregation::make()` now accepts an array of sub-aggregations as well as a single one, so a bucket can hold several sibling aggregations.
- `Percentile::make()` accepts an optional `percents` list, `BucketSort::make()` an optional `order`, `size` and `from`, and `Terms::make()` an optional `order` and `minDocCount`. Existing calls produce the same query as before.
- Full-text queries: `MatchPhrase`, `MultiMatch`, `MatchBoolPrefix`, `QueryString` and `SimpleQueryString`.
- Compound and joining queries: `Nested`, `ConstantScore`, `DisMax`, `Boosting` and `FunctionScore`. They accept other query objects (including `BoolQuery`) wherever the DSL expects a query.
- Geographic queries: `GeoDistance` and `GeoBoundingBox`.
- Specialized and term-level queries: `MoreLikeThis`, `Script`, `TermsSet` and `Knn` (requires the OpenSearch k-NN plugin).
- Metric aggregations: `ValueCount`, `ExtendedStats`, `TopHits`, `PercentileRanks` and `WeightedAvg`.
- Bucket aggregations: `Histogram`, `Filter`, `Filters`, `Missing`, `Nested`, `ReverseNested` and `GlobalBucket` (the `global` aggregation; `global` is a reserved word in PHP).
- Bucket aggregations: `Composite` (with `after` paging), `MultiTerms` and `SignificantTerms`.
- Pipeline aggregations: `BucketSelector`, `BucketScript`, `AvgBucket`, `SumBucket`, `MinBucket`, `MaxBucket`, `StatsBucket`, `CumulativeSum`, `Derivative` and `MovingFunction`.
- Geographic and scripted aggregations: `GeoDistance`, `GeohashGrid`, `GeoBounds`, `GeoCentroid` and `ScriptedMetric`.
- `OpenSearchBuilder` gained `from()`, `source()`, `highlight()` and `trackTotalHits()` for pagination, source filtering, highlighting and total hit counting. Requests that use none of them are unchanged.
- The README now documents the existing `Terms` query and the `Range`, `DateRange` and `DateHistogram` aggregations, and groups the query and aggregation reference by category.

### Changed

- `composer.json` now declares its Laravel dependency (`illuminate/support` and `illuminate/database` `^10.0|^11.0|^12.0|^13.0`). The package always needed Laravel but never said so. Apps on Laravel 9 or older will stay on the previous release instead of installing this one.
- The development dependencies now allow `orchestra/testbench` 8 through 11 and `phpunit/phpunit` 10.5 through 13, so the suite can run against every supported Laravel version.
- `OpenSearchServiceProvider::register()` and `boot()` now declare a `void` return type. Only a class that extends the provider and overrides either method without `: void` is affected.
- `Model::opensearch()` now resolves `OpensearchClientFactory` from the container, where it is registered as a singleton, so applications can swap the client in their tests. The factory builds the client once and reuses it instead of building a new one on every `opensearch()` call. After changing the connection config at runtime, call `forgetClient()` on the factory so the next call picks up the new config.
- `search()` now throws `InvalidSearchParametersException` when given anything other than one `Query` and/or one `Sort`. It used to silently ignore other items, so passing a query node without its `Query` wrapper sent a match-all search, and a second `Query` or `Sort` silently replaced the first. `aggregations()` now throws `InvalidAggregationParametersException` for an empty list, an item that isn't an `Aggregation`, or two aggregations with the same name at the same level (including sub-aggregations); same-named aggregations used to collapse silently into one. Both exceptions extend `InvalidArgumentException` and implement `OpenSearchException`, and they replace the plain `\Exception` the empty and too-many cases used to throw, so existing `catch (\Exception $e)` blocks still catch them.
- `Query::make()` now throws `InvalidSearchParametersException` unless it is given exactly one query type or `BoolQuery`. A second root query used to be silently dropped when it had the same type as the first and merged into a query OpenSearch rejected when it didn't; an empty list sent a query OpenSearch rejected; a non-object item crashed with a PHP `Error`.
- `BoolQuery::make()` now throws `InvalidSearchParametersException` for an item that isn't a `Must`, `Should`, `MustNot` or `Filter` clause, for an option key other than `minimum_should_match` and `boost`, and for a second clause of the same kind. These used to be silently ignored or overwritten, so a bare query node produced a match-all `bool` and a misspelled option was lost. `minimum_should_match` is still left out when there is no `Should` clause.

### Fixed

- The OpenSearch client now authenticates with the configured `password` instead of sending the `username` as the password.
- The `Wildcard` query now emits the `wildcard` key instead of `wildcard` followed by trailing spaces, which OpenSearch rejected.
- The `Regexp` query now emits a `regexp` key instead of `fuzzy`, so it no longer runs as a fuzzy query.
- The `Ids` query now wraps its values in the `ids` key, so it produces a valid query instead of a bare `values` list.
- The `DateHistogram` aggregation now applies its `format` to the `date_histogram` key instead of adding a stray `date_range` key.
- The `indices()->create()` docblock now documents the setting keys the method actually reads (`number_of_shards`, `number_of_replicas`, `refresh_interval`) instead of camelCase names that were silently ignored.
- `documents()->createOrUpdate()` now throws `ModelException` when no model matches the given id, instead of fataling on a null model.
- The README model example now returns the mapping's `properties` directly from `openSearchMapping()` instead of wrapping them in a `mapping` key, which `indices()->create()` sent as `mappings.mapping` and OpenSearch rejected. Its `openSearchArray()` now builds `name` with string interpolation instead of `+`, which throws a `TypeError` in PHP. The mapping gives `name` a `keyword` sub-field and the query example aggregates on `name.keyword`, because OpenSearch rejects terms aggregations on `text` fields.
- A `BoolQuery` with nothing to send, such as `BoolQuery::make([])`, now serialises to `{"bool": {}}` instead of `{"bool": []}`, which OpenSearch rejected as malformed.
- `DateRange::make()` and `DateHistogram::make()` now declare their optional `format` and `offset` parameters as `?string`, removing the implicitly nullable parameter deprecation on PHP 8.4 and later.
- `documents()->create()` with a single id no longer throws `UnexpectedValueException`. It sent `retry_on_conflict`, which the create endpoint rejects, and wrapped the document in a `doc` key that belongs to the update API, so the document would have been stored one level too deep.

## [1.0.2] - 2024-06-12

### Fixed

- The `Range` query now emits the `range` key instead of `prefix`.
- The `Terms` query now takes a field name (`Terms::make(string $field, string|int|array $values)`) and emits a `terms` key, instead of a bare `values` list that OpenSearch rejected.

## [1.0.1] - 2024-04-19

### Fixed

- `OpenSearchCreateException` now carries the full bulk response when indexing fails, instead of only the `errors` flag.
- `documents()->create()` with an array of ids no longer fails with an undefined index when the bulk response has no `errors` key.

## [1.0.0] - 2024-03-13

- First stable release.

[Unreleased]: https://github.com/codeartmk/opensearch-laravel/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/codeartmk/opensearch-laravel/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/codeartmk/opensearch-laravel/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/codeartmk/opensearch-laravel/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/codeartmk/opensearch-laravel/releases/tag/v1.0.0
