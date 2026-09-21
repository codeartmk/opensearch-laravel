<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

/**
 * Marker for aggregation nodes. It is empty: it only lets the `AggregationType` type hint on Aggregation accept
 * custom aggregation classes. A custom aggregation must also implement OpenSearchQuery, which does the actual work.
 */
interface AggregationType
{

}
