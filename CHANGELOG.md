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
- The README now documents the existing `Terms` query and the `Range`, `DateRange` and `DateHistogram` aggregations, and groups the query and aggregation reference by category.

### Changed

- `composer.json` now declares its Laravel dependency (`illuminate/support` and `illuminate/database` `^10.0|^11.0|^12.0|^13.0`). The package always needed Laravel but never said so. Apps on Laravel 9 or older will stay on the previous release instead of installing this one.
- The development dependencies now allow `orchestra/testbench` 8 through 11 and `phpunit/phpunit` 10.5 through 13, so the suite can run against every supported Laravel version.
- `OpenSearchServiceProvider::register()` and `boot()` now declare a `void` return type. Only a class that extends the provider and overrides either method without `: void` is affected.

### Fixed

- The OpenSearch client now authenticates with the configured `password` instead of sending the `username` as the password.
- The `Wildcard` query now emits the `wildcard` key instead of `wildcard` followed by trailing spaces, which OpenSearch rejected.
- The `Regexp` query now emits a `regexp` key instead of `fuzzy`, so it no longer runs as a fuzzy query.
- The `Ids` query now wraps its values in the `ids` key, so it produces a valid query instead of a bare `values` list.
- The `DateHistogram` aggregation now applies its `format` to the `date_histogram` key instead of adding a stray `date_range` key.
- The `indices()->create()` docblock now documents the setting keys the method actually reads (`number_of_shards`, `number_of_replicas`, `refresh_interval`) instead of camelCase names that were silently ignored.
- `documents()->createOrUpdate()` now throws `ModelException` when no model matches the given id, instead of fataling on a null model.
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
