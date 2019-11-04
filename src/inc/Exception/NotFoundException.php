<?php

declare (strict_types = 1);

namespace Bingo\Exception;

/**
 * Exception representing a 404 Not Found HTTP response.
 */
class NotFoundException extends HttpException
{
    /**
     * @param string $message The error message
     */
    public function __construct(string $message = '')
    {
        parent::__construct(404, 'Not Found', $message);
    }
}
