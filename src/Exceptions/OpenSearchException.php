<?php

namespace Codeart\OpensearchLaravel\Exceptions;

/**
 * Implemented by every exception the package throws, so `catch (OpenSearchException $e)` catches them all.
 * Errors from the OpenSearch client itself (connection failures, HTTP errors) are not wrapped and don't implement it.
 */
interface OpenSearchException
{

}
