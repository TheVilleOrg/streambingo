<?php

declare (strict_types = 1);

namespace Bingo\Controller;

use Bingo\Config;
use Bingo\Exception\BadRequestException;
use Bingo\Exception\GameException;
use Bingo\Exception\NotFoundException;
use Bingo\Exception\UnauthorizedException;
use Bingo\Model\CardModel;
use Bingo\Model\GameMetaModel;
use Bingo\Model\GameModel;
use Bingo\Model\UserModel;

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
        return GameModel::loadGameFromName($gameName) ?? self::createGame($userId, $gameName);
    }

    /**
     * Gets a game from the database based on the secret game token associated with the game.
     *
     * @param string $gameToken The secret game token associated with the game
     *
     * @return \Bingo\Model\GameModel The game
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function getGameFromToken(string $gameToken): GameModel
    {
        $game = GameModel::loadGameFromToken($gameToken);
        if (!$game)
        {
            throw new NotFoundException('Game not found');
        }

        return $game;
    }

    /**
     * Gets the number of cards associated with a game as a formatted string.
     *
     * @param string $gameName The unique name identifying the game
     *
     * @return string The number of cards associated with the game as a formatted string
     */
    public static function getCardCount(string $gameName): string
    {
        $count = GameMetaModel::getCardCount($gameName);
        $label = $count === 1 ? 'Player' : 'Players';
        return $count . ' ' . $label;
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
        $game = GameModel::loadGameFromName($gameName);
        if (!$game)
        {
            throw new NotFoundException('Attempted to end a non-existent game');
        }

        $game->setEnded(true)->setWinner($cardId)->save();
    }

    /**
     * Gets a game name from a secret game token.
     *
     * @param string $token The secret game token
     *
     * @return string|null The name of the game, or null if the game does not exist
     */
    public static function getNameFromToken(string $token): ?string
    {
        return GameModel::getNameFromToken($token);
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
        $game = GameModel::loadGameFromName($gameName);
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
     * Gets a card for a game.
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
        $card = CardModel::loadCard($userId, $gameName);
        if (!$card)
        {
            throw new NotFoundException('Card not found');
        }

        return $card;
    }

    /**
     * Creates a new card.
     *
     * @param int $twitchId The Twitch identifier associated with the user creating the card
     * @param string $userName The user name of the user creating the card
     * @param string $gameName The name of the game with which to associate the card
     */
    public static function createCard(int $twitchId, string $userName, string $gameName): void
    {
        if (!GameModel::gameExists($gameName))
        {
            throw new NotFoundException('Attempted to create card for unknown game');
        }

        $userId = UserController::getIdFromTwitchUser($userName, $twitchId);

        if (!CardModel::cardExists($userId, $gameName))
        {
            $card = CardModel::createCard($userId, $gameName);
            $card->save();
        }
    }

    /**
     * Gets the cards for a user.
     *
     * @param int $userId The unique identifier associated with the user that owns the cards
     *
     * @return \Bingo\Model\CardModel[] The cards
     */
    public static function getUserCards(int $userId): array
    {
        return CardModel::loadUserCards($userId);
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
     * @param int $twitchId The Twitch identifier associated with the user that owns the card
     * @param string $gameName The unique name identifying the game associated with the card
     *
     * @return bool|null True if the card meets the win conditions, false otherwise, null if the game does not exist
     */
    public static function submitCard(int $twitchId, string $gameName): ?bool
    {
        $userId = UserModel::getIdFromTwitchId($twitchId);

        $card = CardModel::loadCard($userId, $gameName);
        if (!$card)
        {
            return null;
        }

        $game = GameModel::loadGameFromName($gameName);

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
