<?php

declare (strict_types = 1);

namespace Bingo\Page;

use Bingo\Controller\UserController;
use Bingo\Controller\GameController;
use Bingo\Exception\UnauthorizedException;

/**
 * Represents the handler for the game host page.
 */
class HostPage extends Page
{
	/**
	 * The unique identifier associated with the current user
	 *
	 * @var int
	 */
	protected $userId;

	/**
	 * The unique name identifying the current game
	 *
	 * @var string
	 */
	protected $gameName;

	/**
	 * @inheritDoc
	 *
	 * @throws \Bingo\Exception\UnauthorizedException
	 */
	protected function run(array $params): void
	{
		$user = UserController::getCurrentUser();
		if (!$user)
		{
			$data = [
				'authUrl'	=> UserController::getAuthUrl('host'),
			];
			$this->showTemplate('auth', $data);

			return;
		}

		if (!$user->getHost())
		{
			throw new UnauthorizedException('Your account is not authorized to host games.');
		}

		$this->userId = $user->getId();
		$this->gameName = $user->getName();

		if (\filter_has_var(INPUT_POST, 'action'))
		{
			$this->handleAction();
		}
		else
		{
			$this->showPage();
		}
	}

	/**
	 * Shows the game host page.
	 */
	protected function showPage(): void
	{
		$game = GameController::getGame($this->userId, $this->gameName);

		$called = $game->getCalled();

		$last = '';
		if (!empty($called))
		{
			$last = $called[\count($called) - 1];
			$last = $this->getLetter($last) . $last;
		}

		$data = [
			'scripts'	=> [
				'gamehost',
			],
			'called'	=> $called,
			'last'		=> $last,
		];

		$this->showTemplate('host', $data);
	}

	/**
	 * Handles an action request.
	 *
	 * @throws \Bingo\Exception\NotFoundException
	 */
	protected function handleAction(): void
	{
		$data = [];

		switch (\filter_input(INPUT_POST, 'action'))
		{
			case 'createGame':
				GameController::createGame($this->userId, $this->gameName);
				break;
			case 'callNumber':
				$data['number'] = GameController::callNumber($this->gameName);
				$data['letter'] = $this->getLetter($data['number']);
				break;
		}

		echo \json_encode($data);
	}

	/**
	 * Gets the letter associated with a grid number.
	 *
	 * @param int $number The number
	 *
	 * @return string The letter associated with the number
	 */
	protected function getLetter(int $number): string
	{
		if ($number <= 15)
		{
			return 'B';
		}

		if ($number <= 30)
		{
			return 'I';
		}

		if ($number <= 45)
		{
			return 'N';
		}

		if ($number <= 60)
		{
			return 'G';
		}

		if ($number <= 75)
		{
			return 'O';
		}

		return '';
	}
}
