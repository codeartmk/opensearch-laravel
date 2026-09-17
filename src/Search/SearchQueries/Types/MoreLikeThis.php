<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class MoreLikeThis implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string|array $like Free text, or a list of texts and documents like ['_index' => 'users', '_id' => '1']
     */
    public function __construct(
        private readonly array $fields,
        private readonly string|array $like,
        private readonly ?int $minTermFreq,
        private readonly ?int $maxQueryTerms,
        private readonly ?int $minDocFreq
    ){}

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
