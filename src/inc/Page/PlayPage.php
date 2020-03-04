<?php

declare (strict_types = 1);

namespace Bingo\Page;

use Bingo\Controller\UserController;
use Bingo\Controller\GameController;
use Bingo\Model\GameModel;
use Bingo\Model\UserModel;

/**
 * Represents the handler for the game player page.
 */
class PlayPage extends Page
{
    /**
     * @inheritDoc
     */
    protected function run(array $params): void
    {
        $user = UserController::getCurrentUser();

        if (!$user)
        {
            $this->showTemplate('auth');

            return;
        }

        if (\filter_has_var(INPUT_POST, 'action'))
        {
            $this->handleAction($user);
        }
        else
        {
            $this->showPage($user);
        }
    }

    /**
     * Shows the game player page.

     * @param \Bingo\Model\UserModel $user The user
     */
    protected function showPage(UserModel $user): void
    {
        $data = [
            'scripts'  => [
                'gameclient',
            ],
            'gameToken' => $user->getGameToken(),
            'cards'    => [],
        ];

        $cards = GameController::getUserCards($user->getId());

        foreach ($cards as $card)
        {
            $card = [
                'cardId'     => $card->getId(),
                'gameId'     => $card->getGameId(),
                'gameName'   => \htmlspecialchars($card->getGameName()),
                'grid'       => $card->getGrid(),
                'marked'     => $card->getMarked(),
                'freeSpace'  => $card->getGameType() === GameModel::GAME_TYPE_FREE_LINE || $card->getGameType() === GameModel::GAME_TYPE_FREE_FILL,
                'gameEnded'  => $card->getGameEnded(),
                'gameWinner' => $card->getGameWinner(),
            ];

            if ($card['freeSpace'])
            {
                $card['grid'][12] = 'Free';
            }

            $data['cards'][] = $card;
        }

        $this->showTemplate('play', $data);
    }

    /**
     * Handles an action request.

     * @param \Bingo\Model\UserModel $user The user
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    protected function handleAction(UserModel $user): void
    {
        $data = [];

        switch (\filter_input(INPUT_POST, 'action'))
        {
            case 'markCell':
                $gameId = \filter_input(INPUT_POST, 'gameId', FILTER_VALIDATE_INT);
                $cell = \filter_input(INPUT_POST, 'cell', FILTER_VALIDATE_INT);
                $marked = \filter_input(INPUT_POST, 'marked', FILTER_VALIDATE_BOOLEAN);
                $data['marked'] = GameController::markCell($user->getId(), $gameId, $cell, $marked);
                break;
            case 'fetchCard':
                $gameId = \filter_input(INPUT_POST, 'gameId', FILTER_VALIDATE_INT);
                $card = GameController::getCard($user->getId(), $gameId);
                $data['cardId'] = $card->getId();
                $data['gameName'] = $card->getGameName();
                $data['grid'] = $card->getGrid();
                $data['freeSpace'] = $card->getGameType() === GameModel::GAME_TYPE_FREE_LINE || $card->getGameType() === GameModel::GAME_TYPE_FREE_FILL;

                if ($data['freeSpace'])
                {
                    $data['grid'][12] = 'Free';
                }

                break;
        }

        echo \json_encode($data);
    }
}
