<?php

namespace Codeart\OpensearchLaravel\Exceptions;

/**
 * Thrown by AggregationBuilder, and so by OpenSearchBuilder::aggregations() and a sub-aggregation list,
 * for an empty list, an item that isn't an Aggregation, or two aggregations with the same name at one level.
 */
class InvalidAggregationParametersException extends \InvalidArgumentException implements OpenSearchException
{

}
