<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

/**
 * Marker for search query nodes. It is empty: it only lets type hints such as `SearchQueryType|BoolQuery`
 * accept custom query classes. A custom query must also implement OpenSearchQuery, which does the actual work.
 */
interface SearchQueryType
{

}
