<?php

declare (strict_types = 1);

namespace Bingo\Page;

use Bingo\Controller\UserController;
use Bingo\Controller\GameController;

/**
 * Represents the handler for the game player page.
 */
class PlayPage extends Page
{
    /**
     * The unique identifier associated with the current user
     *
     * @var int
     */
    protected $userId;

    /**
     * The unique name identifying the current game.
     *
     * @var string
     */
    protected $gameName;

    /**
     * @inheritDoc
     */
    protected function run(array $params): void
    {
        if (empty($params))
        {
            return;
        }

        $this->gameName = $params[0];

        $user = UserController::getCurrentUser();

        if (!$user)
        {
            $this->showTemplate('auth');

            return;
        }

        $this->userId = $user->getId();

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
     * Shows the game player page.
     */
    protected function showPage(): void
    {
        $card = GameController::getCard($this->userId, $this->gameName);

        $grid = $card->getGrid();
        $grid[12] = 'Free';

        $data = [
            'scripts'  => [
                'gameclient',
            ],
            'gameName' => \htmlspecialchars($this->gameName),
            'grid'     => $grid,
            'marked'   => $card->getMarked(),
        ];

        $this->showTemplate('play', $data);
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
            case 'toggleCell':
                $cell = \filter_input(INPUT_POST, 'cell', FILTER_VALIDATE_INT);
                $data['marked'] = GameController::toggleCell($this->userId, $this->gameName, $cell);
                break;
        }

        echo \json_encode($data);
    }
}
