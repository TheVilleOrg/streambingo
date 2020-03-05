<?php

/**
 * This file is part of StreamBingo.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 3 (GPL-3.0)
 *
 * For full license information, see the LICENSE file included with the source.
 */

declare (strict_types = 1);

namespace Bingo\Exception;

/**
 * Exception representing a 500 Internal Server Error HTTP response.
 */
class InternalErrorException extends HttpException
{
    /**
     * @param string $message The error message
     */
    public function __construct(string $message = '')
    {
        parent::__construct(500, 'Internal Server Error', $message);
    }
}
