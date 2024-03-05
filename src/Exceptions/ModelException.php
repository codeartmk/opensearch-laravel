<?php

namespace Codeart\OpensearchLaravel\Exceptions;

use JetBrains\PhpStorm\Pure;

class ModelException extends \Exception implements OpenSearchException
{
    #[Pure] public function __construct($message, $code = 500)
    {
        $previous = null;

        parent::__construct($message, $code, $previous);
    }
}
