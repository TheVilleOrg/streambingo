<?php

declare (strict_types = 1);

namespace Bingo\Controller;

use Bingo\App;
use Bingo\Exception\BadRequestException;
use Bingo\Exception\GameException;
use Bingo\Exception\NotFoundException;
use Bingo\Model\CardModel;
use Bingo\Model\GameMetaModel;
use Bingo\Model\GameModel;

/**
 * Provides an interface to the game functionality.
 */
class GameController
{
    /**
     * Gets the metadata for all the games.
     *
     * @return \Bingo\Model\GameMetaModel[] An array of games' metadata
     */
    public static function getGameList(): array
    {
        return GameMetaModel::getGames();
    }

    /**
     * Gets a game, creating a new game if the specified game does not exist.
     *
     * @param int $userId The unique identifier associated with the user that owns the game
     * @param string $gameName The unique name identifying the game
     *
     * @return \Bingo\Model\GameModel The game
     */
    public static function getGame(int $userId, string $gameName): GameModel
    {
        return GameModel::loadGame($gameName) ?? self::createGame($userId, $gameName);
    }

    /**
     * Creates a new game.
     *
     * @param int $userId The unique identifier associated with the user that owns the game
     * @param string $gameName The unique name to identify the game
     *
     * @return \Bingo\Model\GameModel The game
     */
    public static function createGame(int $userId, string $gameName): GameModel
    {
        GameModel::deleteGame($gameName);
        $game = GameModel::createGame($userId, $gameName);
        $game->save();

        return $game;
    }

    /**
     * Ends the specified game.
     *
     * @param string $gameName The unique name identifying the game
     * @param int|null $cardId The unique identifier associated with the winning card, or null if there is no winner
     */
    public static function endGame(string $gameName, int $cardId = null): void
    {
        $game = GameModel::loadGame($gameName);
        if (!$game)
        {
            throw new NotFoundException('Attempted to end a non-existent game');
        }

        $game->setEnded(true)->setWinner($cardId)->save();
    }

    public static function getGameFromToken(string $token): ?string
    {
        return GameModel::getGameFromToken($token);
    }

    /**
     * Gets the URL to get a card.
     *
     * @param string $gameName The unique name identifying the game
     *
     * @return string|null The URL to the card page, or null if the game does not exist
     */
    public static function getGameUrl(string $gameName): ?string
    {
        $game = GameModel::loadGame($gameName);
        if (!$game || $game->getEnded())
        {
            return null;
        }

        return App::getBaseUrl() . 'play/' . $game->getGameName();
    }

    /**
     * Calls a number for the specified game.
     *
     * @param string $gameName The unique name identifying the game
     *
     * @return int The number that was called
     *
     * @throws \Bingo\Exception\BadRequestException
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function callNumber(string $gameName): int
    {
        $game = GameModel::loadGame($gameName);
        if (!$game)
        {
            throw new NotFoundException('Attemped to call number for unknown game');
        }

        try {
            $number = $game->callNumber();
        }
        catch (GameException $e)
        {
            throw new BadRequestException($e->getMessage());
        }

        $game->save();

        return $number;
    }

    /**
     * Gets a card for a game, creating a new card if the specified card does not exist.
     *
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param string $gameName The unique name identifying the game associated with the card
     *
     * @return \Bingo\Model\CardModel The card
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function getCard(int $userId, string $gameName): CardModel
    {
        if (!GameModel::gameExists($gameName))
        {
            throw new NotFoundException('Attempted to get card for unknown game');
        }

        $card = CardModel::loadCard($userId, $gameName);
        if (!$card)
        {
            $card = CardModel::createCard($userId, $gameName);
            $card->save();
        }

        return $card;
    }

    /**
     * Marks a call on a card.
     *
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param string $gameName The unique name identifying the game associated with the card
     * @param int $cell The index of the cell
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function toggleCell(int $userId, string $gameName, int $cell): bool
    {
        $card = CardModel::loadCard($userId, $gameName);
        if (!$card)
        {
            throw new NotFoundException('Attemped to mark unknown card');
        }

        try {
            if ($card->getCellMarked($cell))
            {
                $card->unmark($cell);
            }
            else
            {
                $card->mark($cell);
            }
        }
        catch (GameException $e)
        {
            throw new BadRequestException($e->getMessage());
        }

        $card->save();

        return $card->getCellMarked($cell);
    }

    /**
     * Checks a card against the win conditions, ending the game if it meets the win conditions.
     *
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param string $gameName The unique name identifying the game associated with the card
     *
     * @return bool True if the card meets the win conditions, false otherwise
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function submitCard(int $userId, string $gameName): bool
    {
        $card = CardModel::loadCard($userId, $gameName);
        if (!$card)
        {
            throw new NotFoundException('Attemped to submit unknown card');
        }

        $game = GameModel::loadGame($gameName);

        $result = $card->checkCard($game->getCalled());

        if ($result) {
            self::endGame($gameName, $card->getId());
        }

        return $result;
    }
    /**
     * Gets the letter associated with a grid number.
     *
     * @param int $number The number
     *
     * @return string The letter associated with the number
     */
    public static function getLetter(int $number): string
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
