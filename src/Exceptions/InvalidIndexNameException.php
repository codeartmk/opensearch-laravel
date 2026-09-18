<?php

namespace Codeart\OpensearchLaravel\Exceptions;

/**
 * Thrown when an operation that must address exactly one index is given a name OpenSearch would
 * expand to several indices, or to all of them: an empty name, one containing a wildcard (`*`) or
 * a comma, or `_all`.
 */
class InvalidIndexNameException extends \InvalidArgumentException implements OpenSearchException
{
    public function __construct(string $index, string $operation)
    {
        parent::__construct(
            "$operation needs a single concrete index, but '$index' can match more than one index "
            . '(it is empty, contains a wildcard or a comma, or is _all).'
        );
    }
}
