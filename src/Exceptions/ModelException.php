<?php

namespace Codeart\OpensearchLaravel\Exceptions;

class ModelException extends \Exception implements OpenSearchException
{
    public function __construct($message, $code = 500)
    {
        $previous = null;

        parent::__construct($message, $code, $previous);
    }
}
