<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `more_like_this` query: finds documents similar to the given text or documents.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/specialized/more-like-this/
 */
class MoreLikeThis implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param list<string> $fields The fields to compare
     * @param string|array<array-key, string|array<string, mixed>> $like Free text, or a list of texts and documents
     *       like ['_index' => 'users', '_id' => '1']
     * @param int|null $minTermFreq The minimum number of times a term must occur in the input to be used. Null leaves
     *                              it out so OpenSearch's default (2) applies
     * @param int|null $maxQueryTerms The maximum number of terms picked from the input. Null leaves it out so
     *                                OpenSearch's default (25) applies
     * @param int|null $minDocFreq Terms that occur in fewer documents than this are ignored. Null leaves it out so
     *                             OpenSearch's default (5) applies
     */
    public function __construct(
        private readonly array $fields,
        private readonly string|array $like,
        private readonly ?int $minTermFreq,
        private readonly ?int $maxQueryTerms,
        private readonly ?int $minDocFreq
    ){}

    /**
     * @param list<string> $fields The fields to compare
     * @param string|array<array-key, string|array<string, mixed>> $like Free text, or a list of texts and documents
     *       like ['_index' => 'users', '_id' => '1']
     * @param int|null $minTermFreq The minimum number of times a term must occur in the input to be used. Null leaves
     *                              it out so OpenSearch's default (2) applies
     * @param int|null $maxQueryTerms The maximum number of terms picked from the input. Null leaves it out so
     *                                OpenSearch's default (25) applies
     * @param int|null $minDocFreq Terms that occur in fewer documents than this are ignored. Null leaves it out so
     *                             OpenSearch's default (5) applies
     * @return self
     */
    public static function make(
        array $fields,
        string|array $like,
        ?int $minTermFreq = null,
        ?int $maxQueryTerms = null,
        ?int $minDocFreq = null
    ): self
    {
        return new self($fields, $like, $minTermFreq, $maxQueryTerms, $minDocFreq);
    }

    /**
     * @return array{more_like_this: array{fields: list<string>, like: string|array<array-key, mixed>, min_term_freq?: int, max_query_terms?: int, min_doc_freq?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'more_like_this' => [
                'fields' => $this->fields,
                'like' => $this->like
            ]
        ];

        if (!is_null($this->minTermFreq)) {
            $query['more_like_this']['min_term_freq'] = $this->minTermFreq;
        }

        if (!is_null($this->maxQueryTerms)) {
            $query['more_like_this']['max_query_terms'] = $this->maxQueryTerms;
        }

        if (!is_null($this->minDocFreq)) {
            $query['more_like_this']['min_doc_freq'] = $this->minDocFreq;
        }

        return $query;
    }
}
