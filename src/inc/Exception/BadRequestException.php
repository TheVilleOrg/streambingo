<?php

declare (strict_types = 1);

namespace Bingo\Exception;

/**
 * Exception representing a 400 Bad Request HTTP response.
 */
class BadRequestException extends HttpException
{
	/**
	 * @param string $message The error message
	 */
	public function __construct(string $message = '')
	{
		parent::__construct(400, 'Bad Request', $message);
	}
}
