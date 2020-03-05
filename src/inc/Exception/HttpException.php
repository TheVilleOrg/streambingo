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
 * Base exception for exceptions representing HTTP responses.
 */
class HttpException extends \RuntimeException
{
    /**
     * The error details
     *
     * @var string
     */
    private $details;

    /**
     * @param int $code The HTTP response code
     * @param string $message The HTTP response message
     * @param string $details The error details
     */
    public function __construct(int $code, string $message = '', string $details = '')
    {
        parent::__construct($message, $code, null);
        $this->details = $details;
    }

    /**
     * @return string The error details
     */
    public function getDetails(): string
    {
        return $this->details;
    }
}
