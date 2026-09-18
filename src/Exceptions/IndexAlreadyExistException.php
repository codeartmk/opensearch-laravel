<?php

namespace Codeart\OpensearchLaravel\Exceptions;

class IndexAlreadyExistException extends \Exception implements OpenSearchException
{
    public function __construct($index)
    {
        $message = "The index:$index already exists.";
        $code = 500;
        $previous = null;

        parent::__construct($message, $code, $previous);
    }
}
