<?php

declare (strict_types = 1);

namespace Bingo\Exception;

/**
 * Exception representing an error related to the game.
 */
class GameException extends \RuntimeException
{
	/**
	 * @param string $message The error message
	 */
	public function __construct(string $message = '')
	{
		parent::__construct($message);
	}
}
