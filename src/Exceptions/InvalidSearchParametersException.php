<?php

namespace Codeart\OpensearchLaravel\Exceptions;

/**
 * Thrown while a search is being composed, before anything is sent: by OpenSearchBuilder::search(),
 * by the Query and BoolQuery constructors and by the bool clauses, for input they don't accept.
 */
class InvalidSearchParametersException extends \InvalidArgumentException implements OpenSearchException
{

}
