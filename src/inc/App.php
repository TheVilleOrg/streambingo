<?php

declare (strict_types = 1);

namespace Bingo;

use Bingo\Controller\GameController;
use Bingo\Exception\HttpException;
use Bingo\Page\Page;

/**
 * Provides an interface for the main application.
 */
class App
{
	/**
	 * Runs the main application.
	 */
    public static function run(): void
    {
		\set_exception_handler(['Bingo\App', 'exceptionHandler']);

		$options = [
			'regexp' => '/^[\w\-\/]*$/',
		];
		$page = \filter_input(INPUT_GET, 'page', FILTER_VALIDATE_REGEXP, [ 'options' => $options ]) ?? 'index';
		$page = \explode('/', \rtrim($page, '/'));
		Page::route(\array_shift($page), $page);
	}

	/**
	 * Handles requests from the command line interface.
	 */
	public static function runCli(): void
	{
		global $argc, $argv;
		if (!$argc)
		{
			echo 'usage';
			return;
		}

		$return = [];

		switch ($argv[1])
		{
			case 'getgame':
				$return['name'] = GameController::getGameFromToken($argv[2]);
				break;
			case 'getgameurl':
				$return['url'] = GameController::getGameUrl($argv[2]);
				break;
			case 'submitcard':
				$return['result'] = GameController::submitCard((int) $argv[2], $argv[3]);
				break;
		}

		echo \json_encode($return);
	}

	/**
	 * Handles all uncaught exceptions.
	 *
	 * @param \Throwable $e The exception
	 */
	public static function exceptionHandler(\Throwable $e): void
	{
		$code = 500;
		$message = 'Internal Server Error';
		$details = '';

		if ($e instanceof HttpException)
		{
			$code = $e->getCode();
			$message = $e->getMessage();
			$details = $e->getDetails();
		}
		else
		{
			$details = $e->getMessage();
		}

		\header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code . ' ' . $message);
		if (\filter_input(INPUT_POST, 'json') || \filter_input(INPUT_GET, 'json'))
		{
			\header('Content-Type: application/json');
			echo \json_encode([
				'error'	=> [
					'code'		=> $code,
					'message'	=> $message,
					'details'	=> $details,
				],
			]);
		}
		else
		{
			require __DIR__ . '/views/error.php';
		}
	}
}
