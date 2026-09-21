<?php

namespace Codeart\OpensearchLaravel\Exceptions;

/**
 * Thrown by OpenSearchIndices::create() when the model's index already exists.
 */
class IndexAlreadyExistException extends \Exception implements OpenSearchException
{
    /**
     * @param string $index The resolved index name, prefix included
     */
    public function __construct($index)
    {
        $message = "The index:$index already exists.";
        $code = 500;
        $previous = null;

        parent::__construct($message, $code, $previous);
    }
}
