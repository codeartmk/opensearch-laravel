# Build Opensearch Queries through Eloquent

## Overview

**Note: This package is still in development and is not yet ready for production use**

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

#### We currently support the following Query DSL queries:

### Match

[https://opensearch.org/docs/latest/query-dsl/full-text/match/](https://opensearch.org/docs/latest/query-dsl/full-text/match/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne::make('name', 'john doe');
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

#### We currently support the following aggregation:

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

We plan to support more before we make it production ready.

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

#### Lazy Loading Relationship

The methods `createAll`, `create`, and `createOrUpdate` all accept a function as a second parameter to allow you to lazy 
load your relationship when creating documents.

```php
use App\Models\User;

User::opensearch()
    ->documents()
    ->create($ids, fn($query) => $query->with('relationship'));
```

## License
This project is licensed under the MIT License - see the LICENSE file for details.