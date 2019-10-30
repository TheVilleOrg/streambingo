<?php

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
