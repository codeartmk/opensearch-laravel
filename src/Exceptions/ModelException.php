<?php

namespace Codeart\OpensearchLaravel\Exceptions;

/**
 * Thrown by OpenSearchDocuments::create() and createOrUpdate() when no model has the given id.
 */
class ModelException extends \Exception implements OpenSearchException
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct($message, $code = 500)
    {
        $previous = null;

        parent::__construct($message, $code, $previous);
    }
}
