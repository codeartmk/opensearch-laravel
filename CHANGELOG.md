# Changelog

All notable changes to `codeartmk/opensearch-laravel` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

2.0 contains breaking changes, marked **Breaking** below. See [`UPGRADE.md`](UPGRADE.md) for what to change when
upgrading from 1.x.

### Added

- Per-environment index names: the new `index_prefix` config key (`OPENSEARCH_INDEX_PREFIX`, default empty) is prepended verbatim to every index name the package uses — in searches, `indices()` and `documents()` — so environments can share one cluster (`local_users`, `staging_users`). It applies on top of `openSearchIndexName()`, including overrides of it. An empty prefix keeps the index names unchanged.
- `IndexNameResolver::resolve($model)` returns a model's full index name, prefix included, and `IndexNameResolver::isConcrete($name)` tells whether a name can only address a single index.
- `OpenSearchHealth`, a service class for application health endpoints, resolved from the container: `isReachable()` (ping, never throws), `cluster()` (the raw cluster health response), `index($indexName)` (status, document count, store size and the common index settings as one flat array, with `null` for settings left to the cluster default) and `report($indexNames)` (one summary that never throws when the cluster is unreachable; a call the cluster refuses or fails with an HTTP error, such as a `403` without the monitor privileges, leaves just that part `null`). It takes full index names; use `IndexNameResolver::resolve($model)` for a model's index. `index()` throws `InvalidIndexNameException` for an empty name, `_all`, or a name with a wildcard or a comma, which OpenSearch would answer with the stats of several indices.
- `OpenSearchCreateException` has the getters `getFailedItems()` (each rejected document as `_id`, `status` and `error`), `getIndexedCount()` (documents indexed before the exception, including earlier chunks, which stay in the index) and `getResponse()` (the raw bulk response of the failing request). See **Changed** for the message.
- Every class and public method in `src/` has a complete PHPDoc block, with array shapes, `@throws` and links to the matching OpenSearch documentation.
- `UPGRADE.md`, a guide to upgrading from 1.x.

### Changed

- **Breaking:** the package now requires PHP 8.2 or newer, Laravel 12 or 13 (`illuminate/support` and `illuminate/database` `^12.0|^13.0`) and `opensearch-project/opensearch-php` `^2.7`.
- **Breaking:** `OpensearchClientFactory` now builds the client with opensearch-php's `GuzzleClientFactory` instead of the deprecated `ClientBuilder`, which is removed in opensearch-php 3.0. `guzzlehttp/guzzle` (`^7.8|^8.0`) is therefore a required dependency. Requests are unchanged, but the client now throws `OpenSearch\Exception\*HttpException` classes (e.g. `NotFoundHttpException`, `ConflictHttpException`) instead of the old `OpenSearch\Common\Exceptions\*` ones (e.g. `Missing404Exception`, `Conflict409Exception`), and a connection failure throws Guzzle's `ConnectException`. The old classes extend the new ones, so `catch` blocks naming them no longer match. The basic-auth header is only sent when `opensearch-laravel.username` is set, so clusters without the security plugin no longer receive empty credentials.
- **Breaking:** `builder()->get()` no longer sends `size` unless `size()` was called, so OpenSearch's default of 10 hits applies — the same "only when called" rule `from()`, `source()`, `highlight()` and `trackTotalHits()` already follow. Searches used to return up to 10000 hits implicitly; code that relied on that now silently gets 10, so call `->size(...)` explicitly wherever you need more. Aggregation-only searches should still call `->size(0)`. `get()` no longer throws `InvalidSearchParametersException` when `from()` is used without `size()`: that guard only existed because the old default of 10000 plus any offset exceeded the default result window.
- **Breaking:** `ssl_verification` (`OPENSEARCH_SSL_VERIFICATION`) now defaults to `true`, so the cluster's TLS certificate is verified unless you turn it off. It used to default to `false`, which silently accepted any certificate on a production deploy that forgot the variable. For a local cluster with a self-signed certificate, set `OPENSEARCH_SSL_VERIFICATION=false` explicitly; for a private CA, set it to the path of the CA bundle.
- **Breaking:** `username` and `password` (`OPENSEARCH_USERNAME`, `OPENSEARCH_PASSWORD`) no longer default to `admin`. When no username is set, no credentials are sent. Apps that relied on the `admin`/`admin` fallback must set both variables.
- The two new defaults above only apply if your app never published the config, or re-publishes it: a `config/opensearch-laravel.php` published from 1.x still has `false` and `admin` as its fallbacks, because `mergeConfigFrom()` only fills in missing keys. Update those lines in your copy (or publish it again with `--force`).
- **Breaking:** four aggregation classes in `Codeart\OpensearchLaravel\Aggregations\Types` were renamed so that every class name matches its OpenSearch DSL key, like the rest of the package. The queries they produce are unchanged, and no aliases for the old names are provided — search and replace them:

  | Old class | New class | DSL key |
  |---|---|---|
  | `Average` | `Avg` | `avg` |
  | `Minimum` | `Min` | `min` |
  | `Maximum` | `Max` | `max` |
  | `Percentile` | `Percentiles` | `percentiles` |
- **Breaking:** `OpenSearchCreateException` no longer puts the whole bulk response, JSON-encoded, in its message. The response's per-document errors can quote document values, so they ended up in logs and error trackers. The message is now short and log-safe — the index name, how many documents of the failing chunk were rejected and how many were indexed before the failure — and the details are available through the new getters (see **Added**). The constructor changed to `__construct(string $index, array $response, int $indexedCount)`; code that parsed the old message must switch to the getters.
- **Breaking:** `indices()->delete()` now throws `InvalidIndexNameException` instead of sending the request when the index name is empty, is `_all`, or contains a wildcard (`*`) or a comma — names OpenSearch expands to several indices, all of which it deletes by default.
- **Breaking:** the document `_id` is now the model's primary key (`$model->getKey()`) instead of its `id` attribute, in `createAll()`, `create()` and `createOrUpdate()`. Models with a custom or UUID primary key now index correctly (rebuild their indices after upgrading); models whose key is `id` are unaffected.
- **Breaking:** `documents()->create()` now accepts `int|string|array`, and `createOrUpdate()` and `delete()` accept `int|string`, so string and UUID primary keys can be passed. Subclasses of `OpenSearchDocuments` that override these methods must widen their signatures to match.
- `Ids::make()` now also accepts a single string id, such as a UUID primary key, and wraps it in a one-item list like a single integer id. It used to accept only an integer or an array, so a single string id had to be wrapped in an array by hand. Existing calls produce the same query as before.
- `documents()->createAll()` now pages through the models with `chunkById()` (keyset pagination on the primary key) instead of `chunk()` (`OFFSET` pagination). Rows deleted while an indexing run was in progress used to shift the following pages, so a row could silently never be indexed, and late pages on large tables were slow. Because `chunkById()` orders by the primary key, an `orderBy()` or a join added in the eager-loading callback can conflict with the paging; the callback is meant for eager loading only.
- The development dependencies now allow `orchestra/testbench` 10 and 11 and `phpunit/phpunit` 11.5 through 13. The GitHub Actions workflow tests Laravel 12 on PHP 8.2–8.5 and Laravel 13 on PHP 8.3–8.5.

### Removed

- **Breaking:** support for Laravel 10 and 11, which are past end of life, and for PHP 8.1. Apps on those versions will stay on 1.x.
- **Breaking:** the exception classes `IndexException`, `IndexDoesntExistException`, `ModelConfigurationException` and `SearchFailedException` in `Codeart\OpensearchLaravel\Exceptions`. The package never threw them, so no behaviour changes, but `catch` blocks or `use` statements that name them must be removed. To catch any exception from the package, catch the `OpenSearchException` interface.
- The `#[Pure]` attributes on `ModelException` and `IndexAlreadyExistException`. They came from `jetbrains/phpstorm-attributes`, which was never a dependency.

### Fixed

- `documents()->create()` with a single id that matches no model now throws `ModelException`, like `createOrUpdate()` does. It used to fail with a PHP error on `null`.
- When a username is set without a password, the client now sends an empty password. It would have passed `null`, which Guzzle 8 rejects with an `InvalidArgumentException`; this matters now that the password has no default.

### Security

- TLS certificate verification is on by default, and there are no default credentials: the package no longer tries `admin`/`admin` against whatever host is configured. See **Changed**, including the note on configs published from 1.x.
- `OpenSearchCreateException` messages no longer contain document values (see **Changed**).
- `indices()->delete()` refuses index names that would delete more than one index (wildcards, commas, `_all`), and `OpenSearchHealth::index()` refuses them too.
- The configuration docs now cover the CA-bundle path option for `ssl_verification` and warn against putting credentials in `OPENSEARCH_HOST`.

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
- `OpenSearchBuilder` gained `from()`, `source()`, `highlight()` and `trackTotalHits()` for pagination, source filtering, highlighting and total hit counting. Requests that use none of them are unchanged. `get()` throws `InvalidSearchParametersException` when `from()` is above 0 but `size()` was never called, because the default size of 10000 plus any offset exceeds OpenSearch's default result window.
- The README now documents the existing `Terms` query and the `Range`, `DateRange` and `DateHistogram` aggregations, and groups the query and aggregation reference by category.

### Changed

- `composer.json` now declares its Laravel dependency (`illuminate/support` and `illuminate/database` `^10.0|^11.0|^12.0|^13.0`). The package always needed Laravel but never said so. Apps on Laravel 9 or older will stay on the previous release instead of installing this one.
- The development dependencies now allow `orchestra/testbench` 8 through 11 and `phpunit/phpunit` 10.5 through 13, so the suite can run against every supported Laravel version.
- `OpenSearchServiceProvider::register()` and `boot()` now declare a `void` return type. Only a class that extends the provider and overrides either method without `: void` is affected.
- `Model::opensearch()` now resolves `OpensearchClientFactory` from the container, where it is registered as a singleton, so applications can swap the client in their tests. The factory builds the client once and reuses it instead of building a new one on every `opensearch()` call. After changing the connection config at runtime, call `forgetClient()` on the factory so the next call picks up the new config.
- `search()` now throws `InvalidSearchParametersException` when given anything other than one `Query` and/or one `Sort`. It used to silently ignore other items, so passing a query node without its `Query` wrapper sent a match-all search, and a second `Query` or `Sort` silently replaced the first. `aggregations()` now throws `InvalidAggregationParametersException` for an empty list, an item that isn't an `Aggregation`, or two aggregations with the same name at the same level (including sub-aggregations); same-named aggregations used to collapse silently into one. Both exceptions extend `InvalidArgumentException` and implement `OpenSearchException`, and they replace the plain `\Exception` the empty and too-many cases used to throw, so existing `catch (\Exception $e)` blocks still catch them.
- `Must`, `Should`, `MustNot` and `Filter` now share a `BoolClause` parent class and throw `InvalidSearchParametersException` when a list contains an item that isn't a query type or `BoolQuery`. Such items used to crash with a PHP `Error`, or be sent as invalid clauses when they were other package objects like `Sort`. Their `make()` methods and output are unchanged.
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
- `Filter::make()` now accepts a `BoolQuery` directly, like `Must`, `Should` and `MustNot` already did. It used to throw a `TypeError` unless the `BoolQuery` was wrapped in an array.
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
