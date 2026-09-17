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
    
    // Sent as the index's "mappings", so return its contents directly, starting with "properties".
    public function openSearchMapping(): array
    {
        return [
            "properties" => [
                "id" => [ "type" => "integer" ],
                "first_name" => [ "type" => "text" ],
                "last_name" => [ "type" => "text" ],
                // The keyword sub-field lets you aggregate and sort on name.keyword
                "name" => [ "type" => "text", "fields" => [ "keyword" => [ "type" => "keyword" ] ] ],
                "email" => [ "type" => "keyword" ],
                //...
            ]
        ];
    }
    
    public function openSearchArray(): array
    {
        return [
            "id" => $this->id,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "name" => "{$this->first_name} {$this->last_name}",
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
            aggregationType: Terms::make(field: 'name.keyword',  size: 10000),
            aggregation: Aggregation::make(
                name: 'bucket_truncate',
                aggregationType: BucketSort::make('_key')
            )
        ),
    ])
    ->get();
```

`search()` takes a `Query`, a `Sort`, or one of each, in any order. Anything else, a second `Query` or `Sort`, or an
empty list throws `InvalidSearchParametersException`. `aggregations()` takes an `Aggregation` or a list of them and
throws `InvalidAggregationParametersException` for an empty list, an item that isn't an `Aggregation`, or two
aggregations with the same name at the same level. Both exceptions implement
`Codeart\OpensearchLaravel\Exceptions\OpenSearchException`.

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

### Pagination, source filtering, highlighting and total hits

`size()` defaults to `10000`. The other options are only sent when you call them.

```php
use App\Models\User;

User::opensearch()
    ->builder()
    ->search([
        Query::make([MatchOne::make('bio', 'laravel')]),
    ])
    ->size(20)
    ->from(40) // from + size can't exceed the index's max_result_window (10000 by default)
    ->source(['name', 'email']) // or false, a single field, or ['includes' => [...], 'excludes' => [...]]
    ->highlight(['bio', 'title' => ['fragment_size' => 50]], ['pre_tags' => ['<b>'], 'post_tags' => ['</b>']])
    ->trackTotalHits() // true, false, or a number to count up to
    ->get();
```

- [Paginate results](https://opensearch.org/docs/latest/search-plugins/searching-data/paginate/)
- [Retrieve specific fields](https://opensearch.org/docs/latest/search-plugins/searching-data/retrieve-specific-fields/)
- [Highlight query matches](https://opensearch.org/docs/latest/search-plugins/searching-data/highlight/)
- [Search API (`track_total_hits`)](https://opensearch.org/docs/latest/api-reference/search-apis/search/)

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

#### Terms Set

[https://opensearch.org/docs/latest/query-dsl/term/terms-set/](https://opensearch.org/docs/latest/query-dsl/term/terms-set/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\TermsSet::make('classes', ['CS101', 'CS102', 'MATH101'], minimumShouldMatchField: 'min_required');
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\TermsSet::make('classes', ['CS101', 'CS102'], minimumShouldMatchScript: 'Math.min(params.num_terms, 2)');
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

### Geographic queries

#### Geo Bounding Box

[https://opensearch.org/docs/latest/query-dsl/geo-and-xy/geo-bounding-box/](https://opensearch.org/docs/latest/query-dsl/geo-and-xy/geo-bounding-box/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\GeoBoundingBox::make('location', topLeft: ['lat' => 42.5, 'lon' => 20.5], bottomRight: ['lat' => 41.5, 'lon' => 21.5]);
```

#### Geo Distance

[https://opensearch.org/docs/latest/query-dsl/geo-and-xy/geodistance/](https://opensearch.org/docs/latest/query-dsl/geo-and-xy/geodistance/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\GeoDistance::make('location', lat: 41.99, lon: 21.43, distance: '50km');
```

### Specialized queries

#### k-NN

[https://opensearch.org/docs/latest/query-dsl/specialized/k-nn/index/](https://opensearch.org/docs/latest/query-dsl/specialized/k-nn/index/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Knn::make('embedding', vector: [0.12, 0.45, 0.91], k: 10);
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Knn::make('embedding', vector: [0.12, 0.45, 0.91], k: 10, filter: Term::make('genre', 'drama'));
```

#### More Like This

[https://opensearch.org/docs/latest/query-dsl/specialized/more-like-this/](https://opensearch.org/docs/latest/query-dsl/specialized/more-like-this/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MoreLikeThis::make(['title', 'description'], like: 'the wind rises', minTermFreq: 1, maxQueryTerms: 12);
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\MoreLikeThis::make(['title'], like: [['_index' => 'movies', '_id' => '1']]);
```

#### Script

[https://opensearch.org/docs/latest/query-dsl/specialized/script/](https://opensearch.org/docs/latest/query-dsl/specialized/script/)
```php
\Codeart\OpensearchLaravel\Search\SearchQueries\Types\Script::make("doc['likes'].value > params.min", params: ['min' => 100]);
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

#### Extended Stats

[https://opensearch.org/docs/latest/aggregations/metric/extended-stats/](https://opensearch.org/docs/latest/aggregations/metric/extended-stats/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\ExtendedStats::make('taxful_total_price');
\Codeart\OpensearchLaravel\Aggregations\Types\ExtendedStats::make('taxful_total_price', sigma: 3);
```

#### Geo Bounds

[https://opensearch.org/docs/latest/aggregations/metric/geobounds/](https://opensearch.org/docs/latest/aggregations/metric/geobounds/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\GeoBounds::make('geoip.location');
```

#### Geo Centroid

[https://opensearch.org/docs/latest/aggregations/metric/geocentroid/](https://opensearch.org/docs/latest/aggregations/metric/geocentroid/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\GeoCentroid::make('geoip.location');
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

#### Percentile Ranks

[https://opensearch.org/docs/latest/aggregations/metric/percentile-ranks/](https://opensearch.org/docs/latest/aggregations/metric/percentile-ranks/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\PercentileRanks::make('taxful_total_price', values: [50, 100]);
```

#### Scripted Metric

[https://opensearch.org/docs/latest/aggregations/metric/scripted-metric/](https://opensearch.org/docs/latest/aggregations/metric/scripted-metric/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\ScriptedMetric::make(
    mapScript: "state.total += doc['taxful_total_price'].value",
    combineScript: 'return state.total',
    reduceScript: 'double sum = 0; for (t in states) { sum += t } return sum',
    initScript: 'state.total = 0'
);
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

#### Top Hits

[https://opensearch.org/docs/latest/aggregations/metric/top-hits/](https://opensearch.org/docs/latest/aggregations/metric/top-hits/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\TopHits::make(size: 3);
\Codeart\OpensearchLaravel\Aggregations\Types\TopHits::make(size: 1, sort: [['order_date' => ['order' => 'desc']]], source: ['customer_full_name', 'taxful_total_price']);
```

#### Value Count

[https://opensearch.org/docs/latest/aggregations/metric/value-count/](https://opensearch.org/docs/latest/aggregations/metric/value-count/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\ValueCount::make('taxful_total_price');
```

#### Weighted Average

[https://opensearch.org/docs/latest/aggregations/metric/weighted-avg/](https://opensearch.org/docs/latest/aggregations/metric/weighted-avg/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\WeightedAvg::make(valueField: 'taxful_total_price', weightField: 'total_quantity');
```

### Bucket aggregations

#### Composite

[https://opensearch.org/docs/latest/aggregations/bucket/composite/](https://opensearch.org/docs/latest/aggregations/bucket/composite/)
```php
// A string source is a terms source on that field. Aggregations like Histogram and DateHistogram,
// or raw source arrays, can be used too. Pass the previous response's after_key as `after` to page.
\Codeart\OpensearchLaravel\Aggregations\Types\Composite::make(
    sources: ['category' => 'category.keyword', 'month' => DateHistogram::make('order_date', 'month')],
    size: 100,
    after: ['category' => "Men's Clothing", 'month' => 1672531200000]
);
```

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

#### Filter

[https://opensearch.org/docs/latest/aggregations/bucket/filter/](https://opensearch.org/docs/latest/aggregations/bucket/filter/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Filter::make(Term::make('currency', 'EUR'));
```

#### Filters

[https://opensearch.org/docs/latest/aggregations/bucket/filters/](https://opensearch.org/docs/latest/aggregations/bucket/filters/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Filters::make(['eur' => Term::make('currency', 'EUR'), 'usd' => Term::make('currency', 'USD')], otherBucketKey: 'other');
```

#### Geo Distance

[https://opensearch.org/docs/latest/aggregations/bucket/geo-distance/](https://opensearch.org/docs/latest/aggregations/bucket/geo-distance/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\GeoDistance::make('geoip.location', lat: 41.99, lon: 21.43, ranges: [['to' => 100], ['from' => 100, 'to' => 500]], unit: 'km');
```

#### Geohash Grid

[https://opensearch.org/docs/latest/aggregations/bucket/geohash-grid/](https://opensearch.org/docs/latest/aggregations/bucket/geohash-grid/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\GeohashGrid::make('geoip.location', precision: 4);
```

#### Global

[https://opensearch.org/docs/latest/aggregations/bucket/global/](https://opensearch.org/docs/latest/aggregations/bucket/global/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\GlobalBucket::make(); // named GlobalBucket because `global` is a reserved word in PHP
```

#### Histogram

[https://opensearch.org/docs/latest/aggregations/bucket/histogram/](https://opensearch.org/docs/latest/aggregations/bucket/histogram/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Histogram::make('taxful_total_price', interval: 50, minDocCount: 1);
```

#### Missing

[https://opensearch.org/docs/latest/aggregations/bucket/missing/](https://opensearch.org/docs/latest/aggregations/bucket/missing/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Missing::make('discount');
```

#### Multi Terms

[https://opensearch.org/docs/latest/aggregations/bucket/multi-terms/](https://opensearch.org/docs/latest/aggregations/bucket/multi-terms/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\MultiTerms::make(['region', 'host'], size: 10);
```

#### Nested

[https://opensearch.org/docs/latest/aggregations/bucket/nested/](https://opensearch.org/docs/latest/aggregations/bucket/nested/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Nested::make('products');
```

#### Range

[https://opensearch.org/docs/latest/aggregations/bucket/range/](https://opensearch.org/docs/latest/aggregations/bucket/range/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Range::make('taxful_total_price', [['to' => 50], ['from' => 50, 'to' => 100], ['from' => 100]]);
```

#### Reverse Nested

[https://opensearch.org/docs/latest/aggregations/bucket/reverse-nested/](https://opensearch.org/docs/latest/aggregations/bucket/reverse-nested/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\ReverseNested::make();
\Codeart\OpensearchLaravel\Aggregations\Types\ReverseNested::make('products');
```

#### Significant Terms

[https://opensearch.org/docs/latest/aggregations/bucket/significant-terms/](https://opensearch.org/docs/latest/aggregations/bucket/significant-terms/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\SignificantTerms::make('manufacturer.keyword', size: 5);
```

#### Terms

[https://opensearch.org/docs/latest/aggregations/bucket/terms/](https://opensearch.org/docs/latest/aggregations/bucket/terms/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Terms::make('company.name', 100);
\Codeart\OpensearchLaravel\Aggregations\Types\Terms::make('company.name', 100, order: ['_count' => 'asc'], minDocCount: 5);
```

### Pipeline aggregations

#### Average Bucket

[https://opensearch.org/docs/latest/aggregations/pipeline/avg-bucket/](https://opensearch.org/docs/latest/aggregations/pipeline/avg-bucket/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\AvgBucket::make('sales_per_month>sales');
```

#### Bucket Script

[https://opensearch.org/docs/latest/aggregations/pipeline/bucket-script/](https://opensearch.org/docs/latest/aggregations/pipeline/bucket-script/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\BucketScript::make(['sales' => 'total_sales', 'count' => '_count'], script: 'params.sales / params.count');
```

#### Bucket Selector

[https://opensearch.org/docs/latest/aggregations/pipeline/bucket-selector/](https://opensearch.org/docs/latest/aggregations/pipeline/bucket-selector/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\BucketSelector::make(['sales' => 'total_sales'], script: 'params.sales > 1000');
```

#### Bucket Sort

[https://opensearch.org/docs/latest/aggregations/pipeline/bucket-sort/](https://opensearch.org/docs/latest/aggregations/pipeline/bucket-sort/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\BucketSort::make('company_id');
\Codeart\OpensearchLaravel\Aggregations\Types\BucketSort::make('total_sales', order: 'desc', size: 5, from: 0);
```

#### Cumulative Sum

[https://opensearch.org/docs/latest/aggregations/pipeline/cumulative-sum/](https://opensearch.org/docs/latest/aggregations/pipeline/cumulative-sum/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\CumulativeSum::make('sales');
```

#### Derivative

[https://opensearch.org/docs/latest/aggregations/pipeline/derivative/](https://opensearch.org/docs/latest/aggregations/pipeline/derivative/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\Derivative::make('sales', gapPolicy: 'skip');
```

#### Maximum Bucket

[https://opensearch.org/docs/latest/aggregations/pipeline/max-bucket/](https://opensearch.org/docs/latest/aggregations/pipeline/max-bucket/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\MaxBucket::make('sales_per_month>sales');
```

#### Minimum Bucket

[https://opensearch.org/docs/latest/aggregations/pipeline/min-bucket/](https://opensearch.org/docs/latest/aggregations/pipeline/min-bucket/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\MinBucket::make('sales_per_month>sales');
```

#### Moving Function

[https://opensearch.org/docs/latest/aggregations/pipeline/moving-function/](https://opensearch.org/docs/latest/aggregations/pipeline/moving-function/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\MovingFunction::make('sales', window: 3, script: 'MovingFunctions.unweightedAvg(values)');
```

#### Stats Bucket

[https://opensearch.org/docs/latest/aggregations/pipeline/stats-bucket/](https://opensearch.org/docs/latest/aggregations/pipeline/stats-bucket/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\StatsBucket::make('sales_per_month>sales');
```

#### Sum Bucket

[https://opensearch.org/docs/latest/aggregations/pipeline/sum-bucket/](https://opensearch.org/docs/latest/aggregations/pipeline/sum-bucket/)
```php
\Codeart\OpensearchLaravel\Aggregations\Types\SumBucket::make('sales_per_month>sales');
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

## The client

The OpenSearch client is built from the `opensearch-laravel` config by `OpensearchClientFactory`, which is registered
as a singleton in the container. The client is built on first use and reused for the rest of the request.

If you change the connection config at runtime (for example, per tenant), drop the reused client so the next call
builds one from the new config:

```php
use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;

config(['opensearch-laravel.host' => $tenant->opensearch_host]);

app(OpensearchClientFactory::class)->forgetClient();
```

## Testing

Because `Model::opensearch()` resolves `OpensearchClientFactory` from the container, you can swap the client in your
application's tests instead of sending requests to a real cluster:

```php
use App\Models\User;
use Codeart\OpensearchLaravel\Factories\OpensearchClientFactory;
use Mockery\MockInterface;
use OpenSearch\Client;

public function test_it_searches_users(): void
{
    $client = Mockery::mock(Client::class);
    $client->shouldReceive('search')
        ->once()
        ->andReturn(['hits' => ['total' => ['value' => 0], 'hits' => []]]);

    $this->mock(OpensearchClientFactory::class, function (MockInterface $factory) use ($client) {
        $factory->shouldReceive('createClient')->andReturn($client);
    });

    // Code that calls User::opensearch() now uses the mocked client.
}
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