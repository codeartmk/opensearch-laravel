<?php

namespace Codeart\OpensearchLaravel\Exceptions;

class OpenSearchCreateException extends \Exception implements OpenSearchException
{
    public function __construct($index, $errors)
    {
        $message = "Create for index:$index failed with errors: " . json_encode($errors);
        $code = 500;
        $previous = null;

        parent::__construct($message, $code, $previous);
    }
}
