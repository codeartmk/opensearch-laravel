# Upgrading

## Upgrading from 1.x to 2.0

2.0 drops the Laravel versions that are past end of life, makes the configuration secure by default and settles a
few behaviours that 1.x kept only for backwards compatibility. Most apps need three things: check the config
(including a copy published from 1.x), add `->size(...)` to searches that expect more than 10 hits, and rename the
four aggregation classes. The sections below are ordered by how likely they are to affect you.

The full list of changes is in [`CHANGELOG.md`](CHANGELOG.md).

### Requirements

| | 1.x | 2.0 |
|---|---|---|
| PHP | 8.1+ | **8.2+** (8.3+ on Laravel 13) |
| Laravel | 10, 11, 12, 13 | **12, 13** |
| `opensearch-project/opensearch-php` | `^2.2` | **`^2.7`** |
| `guzzlehttp/guzzle` | not required | **`^7.8\|^8.0`** (Laravel apps already have it) |

Laravel 10 and 11 are past end of life. Apps still on them keep using 1.x — Composer won't install 2.0 there.

```shell
composer require codeartmk/opensearch-laravel:^2.0
```

### Searches return 10 hits unless you call `size()`

**This is the change most likely to affect you, and it does so silently.** 1.x sent `size: 10000` with every search.
2.0 only sends `size` when you call `size()`, so OpenSearch's own default of **10 hits** applies. Nothing throws —
you simply get fewer hits.

Search your code for `->builder()` and add `->size(...)` wherever you need more than 10 hits:

```php
// 1.x: up to 10000 hits
User::opensearch()->builder()->search([Query::make([MatchAll::make()])])->get();

// 2.0: the same call returns 10 hits. Ask for what you need:
User::opensearch()->builder()->search([Query::make([MatchAll::make()])])->size(10000)->get();
```

- Aggregation-only searches should still call `->size(0)`.
- `from()` no longer requires `size()`. 1.x threw `InvalidSearchParametersException` for `from()` without `size()`,
  because the default of 10000 plus any offset exceeded the result window. That guard is gone.
- `from + size` still can't exceed the index's `max_result_window` (10000 by default).

### Configuration: TLS verification and credentials

The defaults are now secure:

| Key (env variable) | 1.x default | 2.0 default |
|---|---|---|
| `ssl_verification` (`OPENSEARCH_SSL_VERIFICATION`) | `false` | **`true`** |
| `username` (`OPENSEARCH_USERNAME`) | `admin` | **not set** — no credentials are sent |
| `password` (`OPENSEARCH_PASSWORD`) | `admin` | **not set** |
| `index_prefix` (`OPENSEARCH_INDEX_PREFIX`) | — | `''` (new, see below) |

**Action required for local setups** that relied on the defaults, e.g. the OpenSearch Docker image with its
self-signed certificate and `admin` user:

```dotenv
OPENSEARCH_SSL_VERIFICATION=false
OPENSEARCH_USERNAME=admin
OPENSEARCH_PASSWORD=your-admin-password
```

For a cluster whose certificate is signed by a private CA, set `OPENSEARCH_SSL_VERIFICATION` to the path of the CA
bundle instead of turning verification off. Use `true`/`false`, not `1`/`0` — `0` is read as a file path. When no
username is set, no `Authorization` header is sent at all, which is what a cluster without the security plugin
expects.

> [!IMPORTANT]
> **If you published the config file in 1.x, the new defaults don't reach your app.** Laravel's `mergeConfigFrom()`
> only fills in keys your published `config/opensearch-laravel.php` doesn't have, so your copy keeps
> `ssl_verification => env('OPENSEARCH_SSL_VERIFICATION', false)` and the `admin`/`admin` fallbacks. Update your copy
> yourself — either replace it with the new one:
>
> ```shell
> php artisan vendor:publish --provider="Codeart\OpensearchLaravel\OpenSearchServiceProvider" --tag="config" --force
> ```
>
> or edit it to match:
>
> ```php
> 'host' => env('OPENSEARCH_HOST', 'http://localhost:9200'),
> 'username' => env('OPENSEARCH_USERNAME'),
> 'password' => env('OPENSEARCH_PASSWORD'),
> 'ssl_verification' => env('OPENSEARCH_SSL_VERIFICATION', true),
> 'index_prefix' => env('OPENSEARCH_INDEX_PREFIX', ''),
> ```

Keep the credentials in `OPENSEARCH_USERNAME`/`OPENSEARCH_PASSWORD`, not in the URL
(`https://user:pass@host`): a URL ends up in config dumps and error reports far more easily than a header.

### Renamed aggregation classes

Four aggregation classes in `Codeart\OpensearchLaravel\Aggregations\Types` were renamed so that every class name
matches its OpenSearch DSL key. The queries they produce are unchanged. There are no aliases for the old names, so
search and replace them:

| 1.x | 2.0 | DSL key |
|---|---|---|
| `Average` | `Avg` | `avg` |
| `Minimum` | `Min` | `min` |
| `Maximum` | `Max` | `max` |
| `Percentile` | `Percentiles` | `percentiles` |

```php
// 1.x
use Codeart\OpensearchLaravel\Aggregations\Types\Average;
Aggregation::make('average_price', Average::make('price'));

// 2.0
use Codeart\OpensearchLaravel\Aggregations\Types\Avg;
Aggregation::make('average_price', Avg::make('price'));
```

### `indices()->delete()` refuses index names that match several indices

`indices()->delete()` now throws `Codeart\OpensearchLaravel\Exceptions\InvalidIndexNameException` — without sending a
request — when the index name is empty, is `_all`, or contains a wildcard (`*`) or a comma. OpenSearch expands such a
name to every matching index and, by default, deletes all of them. This only affects models whose
`openSearchIndexName()` (or `OPENSEARCH_INDEX_PREFIX`) contains one of those; searching such a pattern still works.
Delete the concrete indices by name with the client instead.

### `BoolQuery` checks its option values

`BoolQuery` now throws `InvalidSearchParametersException` when `minimum_should_match` is not an int or a string, or
`boost` is not an int, a float or a numeric string — a clause object under either key included. Such a query was
already rejected by OpenSearch, with one exception: without a Should clause `minimum_should_match` is not sent, so a
clause or a float under that key used to pass silently. Put clauses in the list (or under their own key, `'must' =>`)
and pass `minimum_should_match` as an int or a string. `null` under either key is still allowed and leaves the option
out.

```php
// throws in 2.0
BoolQuery::make([Must::make(...), 'minimum_should_match' => Filter::make(...)]);

// instead
BoolQuery::make([Must::make(...), Filter::make(...)]);
```

### Exceptions of the underlying client

The client is now built with opensearch-php's `GuzzleClientFactory` instead of the deprecated `ClientBuilder`
(removed in opensearch-php 3.0). Requests and responses are unchanged, but **the client throws different exception
classes**. The package lets them through, so this matters if you catch them — for example the conflict that
`documents()->create($id)` raises when the document already exists:

| Situation | 1.x threw | 2.0 throws |
|---|---|---|
| 404, e.g. a missing index or document | `OpenSearch\Common\Exceptions\Missing404Exception` | `OpenSearch\Exception\NotFoundHttpException` |
| 409, e.g. `create()` of an existing document | `OpenSearch\Common\Exceptions\Conflict409Exception` | `OpenSearch\Exception\ConflictHttpException` |
| 400 | `OpenSearch\Common\Exceptions\BadRequest400Exception` | `OpenSearch\Exception\BadRequestHttpException` |
| Other HTTP errors | `OpenSearch\Common\Exceptions\*` | `OpenSearch\Exception\*HttpException` (all implement `OpenSearch\Exception\HttpExceptionInterface`) |
| Cluster unreachable | `OpenSearch\Common\Exceptions\Curl\CouldNotConnectToHost`, `NoNodesAvailableException` | `GuzzleHttp\Exception\ConnectException` (catch `Psr\Http\Client\ClientExceptionInterface`) |

The old classes extend the new ones, so a `catch (Missing404Exception $e)` no longer catches anything. Catch the new
classes instead:

```php
// 1.x
use OpenSearch\Common\Exceptions\Conflict409Exception;

try {
    User::opensearch()->documents()->create($user->id);
} catch (Conflict409Exception $e) {
    // already indexed
}

// 2.0
use OpenSearch\Exception\ConflictHttpException;

try {
    User::opensearch()->documents()->create($user->id);
} catch (ConflictHttpException $e) {
    // already indexed
}
```

`catch (OpenSearch\Common\Exceptions\OpenSearchException $e)` still catches every HTTP error, but not a connection
failure.

### `OpenSearchCreateException`

When a bulk request fails, `OpenSearchCreateException` no longer puts the whole bulk response, JSON-encoded, in its
message — the per-document errors can quote document values, which then ended up in logs. The message now only holds
the index name and counts, and the details moved to getters:

```php
use Codeart\OpensearchLaravel\Exceptions\OpenSearchCreateException;

try {
    User::opensearch()->documents()->createAll();
} catch (OpenSearchCreateException $e) {
    // 1.x: json_decode(substr($e->getMessage(), ...)) to get at the errors
    $e->getFailedItems();  // [['_id' => '5', 'status' => 400, 'error' => [...]], ...] for the failing chunk
    $e->getIndexedCount(); // documents indexed before the failure, earlier chunks included
    $e->getResponse();     // the raw bulk response of the failing request
}
```

- Code that parsed the old message must switch to the getters.
- The constructor changed from `__construct($index, $errors)` to
  `__construct(string $index, array $response, int $indexedCount)`. This only matters if you throw it yourself.
- Treat `getFailedItems()` and `getResponse()` like the documents themselves before logging them.

### Removed exception classes

These classes in `Codeart\OpensearchLaravel\Exceptions` were deleted. The package never threw them, so no behaviour
changes, but `use` statements and `catch` blocks that name them now fail:

- `IndexException`
- `IndexDoesntExistException`
- `ModelConfigurationException`
- `SearchFailedException`

Remove those `catch` blocks. To catch anything the package itself throws, catch the
`Codeart\OpensearchLaravel\Exceptions\OpenSearchException` interface.

### Documents: primary keys, `int|string` ids and paging

**The document `_id` is now the model's primary key** (`$model->getKey()`) instead of its `id` attribute, in
`createAll()`, `create()` and `createOrUpdate()`. For models whose primary key is `id` nothing changes.

For a model with a custom or UUID primary key, 1.x read an `id` attribute such a model usually doesn't have. Bulk
indexing then sent no id and OpenSearch generated a random one, so every run added the documents again. After
upgrading, documents get the primary key as `_id`, which doesn't match what is already in the index, so **rebuild
those indices** rather than indexing on top of them:

```php
User::opensearch()->indices()->delete();
User::opensearch()->indices()->create();
User::opensearch()->documents()->createAll();
```

Related changes:

- `documents()->create()` accepts `int|string|array`, and `createOrUpdate()` and `delete()` accept `int|string`, so
  string and UUID keys can be passed. **If you extend `OpenSearchDocuments` and override these methods, widen your
  signatures to match**, or PHP refuses to load the class.
- `documents()->create()` with a single id that matches no model now throws `ModelException`, like
  `createOrUpdate()` already did. It used to fail with a PHP error.
- `createAll()` now pages with `chunkById()` instead of `chunk()`, so rows deleted while an indexing run is in
  progress no longer cause other rows to be skipped. **`chunkById()` orders by the primary key, so the eager-loading
  callback must not add an `orderBy()` or a join** — use it for `with()` only:

  ```php
  // fine
  User::opensearch()->documents()->createAll(fn($query) => $query->with('roles'));

  // conflicts with the paging — don't
  User::opensearch()->documents()->createAll(fn($query) => $query->orderBy('name'));
  ```

### New features worth adopting

- **Per-environment index names.** Set `OPENSEARCH_INDEX_PREFIX` (e.g. `local_`, `staging_`) and every index the
  package touches — searches, `indices()` and `documents()` — gets the prefix, so several environments can share one
  cluster. The default is empty, so existing index names are unchanged. If you add a prefix to an existing
  environment, the package looks for new indices: create and fill them first (`indices()->create()`,
  `documents()->createAll()`).
- **`OpenSearchHealth`.** A service for your health endpoints: `isReachable()`, `cluster()`, `index($name)` and
  `report($names)`. Use `IndexNameResolver::resolve(new User())` to get a model's full index name, prefix included.
  See [Health checks](README.md#health-checks).
