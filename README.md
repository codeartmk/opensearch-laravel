# Build Opensearch Queries through Eloquent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codeartmk/opensearch-laravel.svg?style=flat-square)](https://packagist.org/packages/codeartmk/opensearch-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/codeartmk/opensearch-laravel.svg?style=flat-square)](https://packagist.org/packages/codeartmk/opensearch-laravel)

## Overview

This package integrates the Opensearch client to work seamlessly with your Laravel Eloquent Model.

## Requirements

| Laravel | PHP |
|---|---|
| 10.x | 8.1 – 8.3 |
| 11.x | 8.2 – 8.4 |
| 12.x | 8.2 – 8.5 |
| 13.x | 8.3 – 8.5 |

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

### Sub-aggregations

The `aggregation` parameter accepts a single `Aggregation` or an array of them, so a bucket can hold several
sibling sub-aggregations:

```php
Aggregation::make(
    name: 'categories',
    aggregationType: Terms::make('category'),
    aggregation: [
        Aggregation::make('average_price', Average::make('price')),
        Aggregation::make('max_price', Maximum::make('price')),
    ]
);
```

## Supported Query DSL queries

### Match All

[https://opensearch.org/docs/latest/query-dsl/match-all/](https://opensearch.org/docs/latest/query-dsl/match-all/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchAll::make();
```

### Full-text queries

#### Match

[https://opensearch.org/docs/latest/query-dsl/full-text/match/](https://opensearch.org/docs/latest/query-dsl/full-text/match/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchOne::make('name', 'john doe');
```

#### Match Bool Prefix

[https://opensearch.org/docs/latest/query-dsl/full-text/match-bool-prefix/](https://opensearch.org/docs/latest/query-dsl/full-text/match-bool-prefix/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchBoolPrefix::make('title', 'the wind rises');
```

#### Match Phrase

[https://opensearch.org/docs/latest/query-dsl/full-text/match-phrase/](https://opensearch.org/docs/latest/query-dsl/full-text/match-phrase/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchPhrase::make('title', 'the wind rises');
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchPhrase::make('title', 'wind rises the', slop: 3);
```

#### Match Phrase Prefix

[https://opensearch.org/docs/latest/query-dsl/full-text/match-phrase-prefix/](https://opensearch.org/docs/latest/query-dsl/full-text/match-phrase-prefix/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MatchPhrasePrefix::make('title', 'the rise');
```

#### Multi Match

[https://opensearch.org/docs/latest/query-dsl/full-text/multi-match/](https://opensearch.org/docs/latest/query-dsl/full-text/multi-match/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MultiMatch::make('wind', ['title^4', 'description']);
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MultiMatch::make('wind rises', ['title', 'description'], type: 'cross_fields', operator: 'and');
```

#### Query String

[https://opensearch.org/docs/latest/query-dsl/full-text/query-string/](https://opensearch.org/docs/latest/query-dsl/full-text/query-string/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\QueryString::make('the wind AND (rises OR rising)');
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\QueryString::make('wind rises', fields: ['title', 'description'], defaultOperator: 'AND');
```

#### Simple Query String

[https://opensearch.org/docs/latest/query-dsl/full-text/simple-query-string/](https://opensearch.org/docs/latest/query-dsl/full-text/simple-query-string/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\SimpleQueryString::make('"rises wind" | windy');
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\SimpleQueryString::make('wind rises', fields: ['title'], defaultOperator: 'AND');
```

### Term-level queries

#### Exists

[https://opensearch.org/docs/latest/query-dsl/term/exists/](https://opensearch.org/docs/latest/query-dsl/term/exists/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Exists::make('description');
```

#### Fuzzy

[https://opensearch.org/docs/latest/query-dsl/term/fuzzy/](https://opensearch.org/docs/latest/query-dsl/term/fuzzy/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Fuzzy::make('speaker', 'HALET');
```

#### IDs

[https://opensearch.org/docs/latest/query-dsl/term/ids/](https://opensearch.org/docs/latest/query-dsl/term/ids/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Ids::make([34229, 91296]);
```

#### Prefix

[https://opensearch.org/docs/latest/query-dsl/term/prefix/](https://opensearch.org/docs/latest/query-dsl/term/prefix/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Prefix::make('speaker', 'KING H');
```

#### Range

[https://opensearch.org/docs/latest/query-dsl/term/range/](https://opensearch.org/docs/latest/query-dsl/term/range/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Range::make('line_id', ['gte' => 10, 'lte' => 20]);
```

#### Regexp

[https://opensearch.org/docs/latest/query-dsl/term/regexp/](https://opensearch.org/docs/latest/query-dsl/term/regexp/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Regexp::make('play_name', '[a-zA-Z]amlet');
```

#### Term

[https://opensearch.org/docs/latest/query-dsl/term/term/](https://opensearch.org/docs/latest/query-dsl/term/term/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Term::make('id', 1234);
```

#### Terms

[https://opensearch.org/docs/latest/query-dsl/term/terms/](https://opensearch.org/docs/latest/query-dsl/term/terms/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Terms::make('line_id', [61809, 61810]);
```

#### Wildcard

[https://opensearch.org/docs/latest/query-dsl/term/wildcard/](https://opensearch.org/docs/latest/query-dsl/term/wildcard/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Wildcard::make('speaker', 'H*Y');
```

### Compound queries

#### Boosting

[https://opensearch.org/docs/latest/query-dsl/compound/boosting/](https://opensearch.org/docs/latest/query-dsl/compound/boosting/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Boosting::make(positive: MatchOne::make('title', 'wind'), negative: Term::make('genre', 'horror'), negativeBoost: 0.2);
```

#### Constant Score

[https://opensearch.org/docs/latest/query-dsl/compound/constant-score/](https://opensearch.org/docs/latest/query-dsl/compound/constant-score/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\ConstantScore::make(Term::make('genre', 'drama'), boost: 1.2);
```

#### Disjunction Max

[https://opensearch.org/docs/latest/query-dsl/compound/disjunction-max/](https://opensearch.org/docs/latest/query-dsl/compound/disjunction-max/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\DisMax::make([MatchOne::make('title', 'wind'), MatchOne::make('description', 'wind')], tieBreaker: 0.7);
```

#### Function Score

[https://opensearch.org/docs/latest/query-dsl/compound/function-score/](https://opensearch.org/docs/latest/query-dsl/compound/function-score/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\FunctionScore::make(
    functions: [
        ['filter' => Term::make('genre', 'drama'), 'weight' => 2],
        ['field_value_factor' => ['field' => 'likes', 'modifier' => 'log1p']],
    ],
    query: MatchOne::make('title', 'wind'),
    scoreMode: 'sum',
    boostMode: 'multiply'
);
```

### Joining queries

#### Nested

[https://opensearch.org/docs/latest/query-dsl/joining/nested/](https://opensearch.org/docs/latest/query-dsl/joining/nested/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Nested::make('comments', Term::make('comments.author', 'ana'));
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Nested::make('comments', Term::make('comments.author', 'ana'), scoreMode: 'max', innerHits: []);
```

## Supported Aggregations

### Metric aggregations

#### Average

[https://opensearch.org/docs/latest/aggregations/metric/average/](https://opensearch.org/docs/latest/aggregations/metric/average/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Average::make('taxful_total_price');
```

#### Cardinality

[https://opensearch.org/docs/latest/aggregations/metric/cardinality/](https://opensearch.org/docs/latest/aggregations/metric/cardinality/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Cardinality::make('products.product_id');
```

#### Maximum

[https://opensearch.org/docs/latest/aggregations/metric/maximum/](https://opensearch.org/docs/latest/aggregations/metric/maximum/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Maximum::make('taxful_total_price');
```

#### Minimum

[https://opensearch.org/docs/latest/aggregations/metric/minimum/](https://opensearch.org/docs/latest/aggregations/metric/minimum/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Minimum::make('taxful_total_price');
```

#### Percentile

[https://opensearch.org/docs/latest/aggregations/metric/percentile/](https://opensearch.org/docs/latest/aggregations/metric/percentile/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Percentile::make('taxful_total_price');
\Codeart\OpensearchLaravel\Aggregations\Types\Percentile::make('taxful_total_price', percents: [50, 95, 99]);
```

#### Stats

[https://opensearch.org/docs/latest/aggregations/metric/stats/](https://opensearch.org/docs/latest/aggregations/metric/stats/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Stats::make('taxful_total_price');
```

#### Sum

[https://opensearch.org/docs/latest/aggregations/metric/sum/](https://opensearch.org/docs/latest/aggregations/metric/sum/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Sum::make('taxful_total_price');
```

### Bucket aggregations

#### Date Histogram

[https://opensearch.org/docs/latest/aggregations/bucket/date-histogram/](https://opensearch.org/docs/latest/aggregations/bucket/date-histogram/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\DateHistogram::make('order_date', 'month');
\Codeart\OpensearchLaravel\Aggregations\Types\DateHistogram::make('order_date', '30d', isIntervalFixed: true, format: 'yyyy-MM-dd');
```

#### Date Range

[https://opensearch.org/docs/latest/aggregations/bucket/date-range/](https://opensearch.org/docs/latest/aggregations/bucket/date-range/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\DateRange::make('order_date', [['to' => 'now-10M/M'], ['from' => 'now-10M/M']], format: 'MM-yyyy');
```

#### Range

[https://opensearch.org/docs/latest/aggregations/bucket/range/](https://opensearch.org/docs/latest/aggregations/bucket/range/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Range::make('taxful_total_price', [['to' => 50], ['from' => 50, 'to' => 100], ['from' => 100]]);
```

#### Terms

[https://opensearch.org/docs/latest/aggregations/bucket/terms/](https://opensearch.org/docs/latest/aggregations/bucket/terms/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Terms::make('company.name', 100);
\Codeart\OpensearchLaravel\Aggregations\Types\Terms::make('company.name', 100, order: ['_count' => 'asc'], minDocCount: 5);
```

### Pipeline aggregations

#### Bucket Sort

[https://opensearch.org/docs/latest/aggregations/pipeline/bucket-sort/](https://opensearch.org/docs/latest/aggregations/pipeline/bucket-sort/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\BucketSort::make('company_id');
\Codeart\OpensearchLaravel\Aggregations\Types\BucketSort::make('total_sales', order: 'desc', size: 5, from: 0);
```

If something you need is missing, see [Extending the functionality](#extending-the-functionality).

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
If you were to implement the [Span term](https://opensearch.org/docs/latest/query-dsl/span/span-term/) query
it would look like the following:
```php
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;
use Codeart\OpensearchLaravel\Search\SearchQueries\Types\SearchQueryType;

class SpanTerm implements OpenSearchQuery, SearchQueryType
{
    public function __construct(
        private readonly string $field,
        private readonly string $value
    ) {}

    public static function make(string $field, string $value): self
    {
        return new self($field, $value);
    }

    public function toOpenSearchQuery(): array
    {
        return [
            'span_term' => [
                $this->field => $this->value
            ]
        ];
    }
}
```

and then just call it.
```php
use App\Models\User;
use MyNamespace\SpanTerm;

User::opensearch()
    ->builder()
    ->search([
        Query::make([
            SpanTerm::make('title', 'wind')
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