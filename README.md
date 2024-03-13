# Build Opensearch Queries through Eloquent

## Overview

This package integrates the Opensearch client to work seamlessly with your Laravel Eloquent Model.

## Installation

To install the Laravel OpenSearch Plugin, use Composer:
```shell
composer require codeartmk/opensearch-laravel
```
and then export the configuration with
```shell
php artisan vendor:publish --provider="Codeart\OpensearchLaravel\OpenSearchServiceProvider" --tag="config"
```

## Basic usage

### Setting up the model

Your models will need to implement the `Codeart\OpensearchLaravel\OpenSearchable` interface, and include the trait 
`Codeart\OpensearchLaravel\Traits\HasOpenSearchDocuments`.

```php
use Codeart\OpensearchLaravel\OpenSearchable;
use Codeart\OpensearchLaravel\Traits\HasOpenSearchDocuments;

class User extends Authenticatable implements OpenSearchable
{
    use HasApiTokens, HasFactory, Notifiable, HasOpenSearchDocuments;
    
    //rest of the model
}
```

You can override the 3 functions `openSearchMapping`, `openSearchArray`, and `openSearchIndexName` to customize your
mapping, the information stored and the index name.

For mapping options look at OpenSearch [mapping documentation](https://opensearch.org/docs/latest/field-types/).

```php
use Codeart\OpensearchLaravel\OpenSearchable;
use Codeart\OpensearchLaravel\Traits\HasOpenSearchDocuments;

class User extends Authenticatable implements OpenSearchable
{
    use HasApiTokens, HasFactory, Notifiable, HasOpenSearchDocuments;
    
    public function openSearchMapping(): array
    {
        return [
            "mapping" => [
                "properties" => [
                    "id" => [ "type" => "integer" ],
                    "first_name" => [ "type" => "text" ],
                    "last_name" => [ "type" => "text" ],
                    "name" => [ "type" => "text" ],
                    "email" => [ "type" => "keyword" ],
                    //...
                ]
            ]
        ];
    }
    
    public function openSearchArray(): array
    {
        return [
            "id" => $this->id,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "name" => $this->first_name + " " + $this->last_name,
            "email" => $this->email,
            //...
        ];
    }
    
    public function openSearchIndexName(): string
    {
        return "users";        
    }
    
    //rest of the model
}
```

## Building queries and aggregations

Once the model is ready you can start building your queries and aggregation through the `opensearch` method on the class:

```php
use App\Models\User;

User::opensearch()
    ->builder()
    ->search([
        Query::make([
            BoolQuery::make([
                Must::make([
                    MatchOne::make("first_name", "John"),
                    BoolQuery::make([
                        Should::make([
                            MatchOne::make('email', 'johndoe@example.com'),
                            MatchOne::make('last_name', 'johndoe@example.com'),
                        ]),
                        'minimum_should_match' => 1
                    ])
                ]),
            ])
        ]),
        Sort::make([
            'id' => 'desc',
        ])
    ])
    ->aggregations([
        Aggregation::make(
            name: "user_names",
            aggregationType: Terms::make(field: 'name',  size: 10000),
            aggregation: Aggregation::make(
                name: 'bucket_truncate',
                aggregationType: BucketSort::make('_key')
            )
        ),
    ])
    ->get();
```

## Supported Query DSL queries:

### Match

[https://opensearch.org/docs/latest/query-dsl/full-text/match/](https://opensearch.org/docs/latest/query-dsl/full-text/match/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne::make('name', 'john doe');
```

### Exists

[https://opensearch.org/docs/latest/query-dsl/term/exists/](https://opensearch.org/docs/latest/query-dsl/term/exists/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Exists::make('description');
```

### Fuzzy

[https://opensearch.org/docs/latest/query-dsl/term/fuzzy/](https://opensearch.org/docs/latest/query-dsl/term/fuzzy/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Fuzzy::make('speaker', 'HALET');
```

### IDs

[https://opensearch.org/docs/latest/query-dsl/term/ids/](https://opensearch.org/docs/latest/query-dsl/term/ids/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Ids::make([34229, 91296]);
```

### Prefix

[https://opensearch.org/docs/latest/query-dsl/term/prefix/](https://opensearch.org/docs/latest/query-dsl/term/prefix/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Prefix::make('speaker', 'KING H');
```

### Range

[https://opensearch.org/docs/latest/query-dsl/term/range/](https://opensearch.org/docs/latest/query-dsl/term/range/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Range::make('line_id', ['gte' => 10, 'lte' => 20]);
```

### Regexp

[https://opensearch.org/docs/latest/query-dsl/term/regexp/](https://opensearch.org/docs/latest/query-dsl/term/regexp/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Regexp::make('play_name', '[a-zA-Z]amlet');
```

### Wildcard

[https://opensearch.org/docs/latest/query-dsl/term/wildcard/](https://opensearch.org/docs/latest/query-dsl/term/wildcard/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Wildcard::make('speaker', 'H*Y');
```

### Match All

[https://opensearch.org/docs/latest/query-dsl/match-all/](https://opensearch.org/docs/latest/query-dsl/match-all/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchAll::make();
```

### Match Phrase Prefix

[https://opensearch.org/docs/latest/query-dsl/full-text/match-phrase-prefix/](https://opensearch.org/docs/latest/query-dsl/full-text/match-phrase-prefix/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchPhrasePrefix::make('title', 'the rise');
```

### Term

[https://opensearch.org/docs/latest/query-dsl/term/term/](https://opensearch.org/docs/latest/query-dsl/term/term/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term::make('id', 1234);
```

## Supported Aggregations

### Average

[https://opensearch.org/docs/latest/aggregations/metric/average/](https://opensearch.org/docs/latest/aggregations/metric/average/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Average::make('taxful_total_price');
```

### Cardinality

[https://opensearch.org/docs/latest/aggregations/metric/cardinality/](https://opensearch.org/docs/latest/aggregations/metric/cardinality/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Cardinality::make('products.product_id');
```

### Maximum

[https://opensearch.org/docs/latest/aggregations/metric/maximum/](https://opensearch.org/docs/latest/aggregations/metric/maximum/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Maximum::make('taxful_total_price');
```

### Minimum

[https://opensearch.org/docs/latest/aggregations/metric/minimum/](https://opensearch.org/docs/latest/aggregations/metric/minimum/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Minimum::make('taxful_total_price');
```

### Percentile

[https://opensearch.org/docs/latest/aggregations/metric/percentile/](https://opensearch.org/docs/latest/aggregations/metric/percentile/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Percentile::make('taxful_total_price');
```

### Stats

[https://opensearch.org/docs/latest/aggregations/metric/stats/](https://opensearch.org/docs/latest/aggregations/metric/stats/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Stats::make('taxful_total_price');
```

### Sum

[https://opensearch.org/docs/latest/aggregations/metric/sum/](https://opensearch.org/docs/latest/aggregations/metric/sum/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Sum::make('taxful_total_price');
```

### Terms

[https://opensearch.org/docs/latest/aggregations/bucket/terms/](https://opensearch.org/docs/latest/aggregations/bucket/terms/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Terms::make('company.name', 100);
```

### Bucket Sort

[https://opensearch.org/docs/latest/aggregations/pipeline-agg/#bucket_sort](https://opensearch.org/docs/latest/aggregations/pipeline-agg/#bucket_sort)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\BucketSort::make('company_id')
```

We plan to support more in the feature.

## Working with indices and documents

We offer tools to help you work with Opensearch indices and documents.

### Indices

We have the methods `create`, `exists`, and `delete` currently.

The optional `$configuration` parameter in the `create` method allows you to customize your 
[settings](https://opensearch.org/docs/latest/install-and-configure/configuring-opensearch/index-settings/#specifying-a-setting-when-creating-an-index) 
for your index.

```php
use App\Models\User;

User::opensearch()
    ->indices()
    ->create($configuration = []);

User::opensearch()
    ->indices()
    ->delete();

User::opensearch()
    ->indices()
    ->exists();
```

### Documents

```php
use App\Models\User;

User::opensearch()
    ->documents()
    ->createAll();

User::opensearch()
    ->documents()
    ->create($ids);

User::opensearch()
    ->documents()
    ->createOrUpdate($id);

User::opensearch()
    ->documents()
    ->delete($id);
```

### Lazy Loading Relationship

The methods `createAll`, `create`, and `createOrUpdate` all accept a function as a second parameter to allow you to lazy 
load your relationship when creating documents.

```php
use App\Models\User;

User::opensearch()
    ->documents()
    ->create($ids, fn($query) => $query->with('relationship'));
```

## Extending the functionality

If we've missed a search query you need or an aggregation you need, you can easily implement your own
and integrate it to work our core functionality.

### Search Query

Create a custom class and implement the `SearchQueryType` and `OpenSearchQuery` interfaces.
If you were to implement the [Query String](https://opensearch.org/docs/latest/query-dsl/full-text/query-string/) query
it would look like the following:
```php
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

class MyCustomQuery implements OpenSearchQuery, SearchQueryType
{
    public function __construct(
        private readonly string $query
    ) {}
    
    public static function make(string $query) {
        return self($query);
    }

    public function toOpenSearchQuery() : array{
        return [
            'query_string' => [
                'query' => $query
            ]  
        ];
    }
}
```

and then just call it.
```php
use App\Models\User;
use MyNamespace\MyCustomQuery;

User::opensearch()
    ->builder()
    ->search([
        Query::make([
            MyCustomQuery::make('the wind AND (rises OR rising)')
        ]),
    ])
    ->get();
```

### Aggregations

You can achieve the same for aggregations but instead of `SearchQueryType` you need to implement the
`AggregationType` inteface.

```php
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Aggregations\Types\AggregationType;

class MyCustomAggregation implements OpenSearchQuery, AggregationType
{
    //aggregation logic
}
```

## Contact Us
[<img src="https://codeart.s3.amazonaws.com/banner.gif">](https://codeart.mk/track/github-opensearch-laravel/)

## License
This project is licensed under the MIT License - see the LICENSE file for details.