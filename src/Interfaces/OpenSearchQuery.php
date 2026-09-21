<?php

namespace Codeart\OpensearchLaravel\Interfaces;

/**
 * Implemented by every node of the request body: queries, clauses, aggregations and their builders.
 * A custom query or aggregation implements it together with the SearchQueryType or AggregationType marker.
 */
interface OpenSearchQuery
{
    /**
     * The node as the array the client serialises to JSON, normally a single-key array named after its
     * OpenSearch DSL key (the bool clauses return their value instead, see BoolClause). Empty JSON objects
     * are returned as `stdClass`, since `[]` would serialise to a JSON array.
     *
     * @return array<array-key, mixed>
     */
    public function toOpenSearchQuery(): array;
}
