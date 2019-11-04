<?php

declare (strict_types = 1);

namespace Bingo\Exception;

/**
 * Exception representing a 401 Unauthorized HTTP response.
 */
class UnauthorizedException extends HttpException
{
    /**
     * @param string $message The error message
     */
    public function __construct(string $message = '')
    {
        parent::__construct(401, 'Unauthorized', $message);
    }
}
