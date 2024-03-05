<?php

namespace Codeart\OpensearchLaravel\Exceptions;

use JetBrains\PhpStorm\Pure;

class IndexAlreadyExistException extends \Exception implements OpenSearchException
{
    #[Pure] public function __construct($index)
    {
        $message = "The index:$index already exists.";
        $code = 500;
        $previous = null;

        parent::__construct($message, $code, $previous);
    }
}
