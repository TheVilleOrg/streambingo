<?php

declare (strict_types = 1);

namespace Bingo\Controller;

use Bingo\Config;
use Bingo\Exception\BadRequestException;
use Bingo\Exception\GameException;
use Bingo\Exception\InternalErrorException;
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
        return GameMetaModel::loadActiveGames();
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
     * Gets the metadata for a game.
     *
     * @param \Bingo\Model\GameModel $game The game
     *
     * @return \Bingo\Model\GameMetaModel The metadata
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function getGameMetaData(GameModel $game): GameMetaModel
    {
        $meta = GameMetaModel::loadGameFromId($game->getId());
        if (!$meta)
        {
            throw new NotFoundException('Attempted to get metadata for an unknown game');
        }

        return $meta;
    }

    /**
     * Creates a new game.
     *
     * @param int $userId The unique identifier associated with the user that owns the game
     * @param string $gameName The unique name to identify the game
     * @param int $autoCall The auto call interval in seconds, or 0 to disable
     */
    public static function createGame(int $userId, string $gameName, int $autoCall): void
    {
        $oldGameId = null;

        $game = GameMetaModel::loadGameFromName($gameName);
        if ($game)
        {
            $oldGameId = $game->getId();
            GameModel::deleteGame($gameName);
        }

        $game = GameModel::createGame($userId, $gameName);
        $game->setAutoCall($autoCall)->save();

        $request = [
            'action'   => 'resetGame',
            'gameName' => $gameName,
            'gameId'   => $oldGameId,
        ];
        self::serverRequest($request);
    }

    /**
     * Ends the specified game.
     *
     * @param string $gameName The unique name identifying the game
     * @param int|null $cardId The unique identifier associated with the winning card, or null if there is no winner
     * @param string|null $winnerName The name of the winning user, or null if there is no winner
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function endGame(string $gameName, int $cardId = null, string $winnerName = null): void
    {
        $game = GameModel::loadGameFromName($gameName);
        if (!$game)
        {
            throw new NotFoundException('Attempted to end a non-existent game');
        }

        $game->setEnded(true)->setWinner($cardId)->setWinnerName($winnerName)->save();
    }

    /**
     * Calls a number for the specified game.
     *
     * @param string $gameName The unique name identifying the game
     *
     * @throws \Bingo\Exception\BadRequestException
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function callNumber(string $gameName): void
    {
        $game = GameModel::loadGameFromName($gameName);
        if (!$game)
        {
            throw new NotFoundException('Attemped to call number for unknown game');
        }

        if (\time() - $game->getUpdated() < 5)
        {
            throw new BadRequestException('Attempted to call a number for a game too soon after the previous');
        }

        try {
            $number = $game->callNumber();
        }
        catch (GameException $e)
        {
            throw new BadRequestException($e->getMessage());
        }

        $game->save();

        $request = [
            'action'   => 'callNumber',
            'gameName' => $gameName,
            'gameId'   => $game->getId(),
            'letter'   => self::getLetter($number),
            'number'   => $number,
        ];
        self::serverRequest($request);
    }

    /**
     * Set the auto call setting for a game.
     *
     * @param \Bingo\Model\GameModel $game The game
     * @param int $interval The auto call interval in seconds
     */
    public static function setAutoCall(GameModel $game, int $interval): void
    {
        $game->setAutoCall($interval)->save();
    }

    /**
     * Gets a card for a game.
     *
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param int $gameId The unique identifier of the game associated with the card
     *
     * @return \Bingo\Model\CardModel The card
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function getCard(int $userId, int $gameId): CardModel
    {
        $card = CardModel::loadCard($userId, $gameId);
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
     *
     * @return int The unique identifier of the game
     *
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function createCard(int $twitchId, string $userName, string $gameName): int
    {
        $game = GameMetaModel::loadGameFromName($gameName);
        if (!$game)
        {
            throw new NotFoundException('Attempted to create card for unknown game');
        }

        $userId = UserController::getIdFromTwitchUser($userName, $twitchId);

        if (!CardModel::cardExists($userId, $game->getId()))
        {
            $card = CardModel::createCard($userId, $game->getId());
            $card->save();

            return $game->getId();
        }

        return 0;
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
     * Marks a cell on a card.
     *
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param int $gameId The unique identifier of the game associated with the card
     * @param int $cell The index of the cell
     *
     * @throws \Bingo\Exception\BadRequestException
     * @throws \Bingo\Exception\NotFoundException
     */
    public static function toggleCell(int $userId, int $gameId, int $cell): bool
    {
        $card = CardModel::loadCard($userId, $gameId);
        if (!$card)
        {
            throw new NotFoundException('Attemped to mark unknown card');
        }

        if ($card->getGameEnded())
        {
            throw new BadRequestException('Attempted to mark a card for a game that has ended');
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
     * @return array|null Array containing the result and the unique game identifier, null if the game does not exist
     */
    public static function submitCard(int $twitchId, string $gameName): ?array
    {
        $user = UserModel::loadUserFromTwitchId($twitchId);
        if (!$user)
        {
            return null;
        }

        $game = GameModel::loadGameFromName($gameName);
        if (!$game)
        {
            return null;
        }

        $card = CardModel::loadCard($user->getId(), $game->getId());
        if (!$card)
        {
            return null;
        }

        $result = $card->checkCard($game->getCalled());

        if ($result) {
            self::endGame($gameName, $card->getId(), $user->getName());
        }

        return [
            'result' => $result,
            'gameId' => $game->getId(),
        ];
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

    /**
     * Make an HTTP POST request to the Node server.
     *
     * @param array $data The POST variables to send
     */
    protected static function serverRequest(array $data = []): void
    {
        $ch = \curl_init(Config::SERVER_URI);
        \curl_setopt($ch, CURLOPT_POST, true);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . Config::SERVER_SECRET,
            'Content-Type: application/json',
        ]);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($data));

        \curl_exec($ch);
        $responseCode = \curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        \curl_close($ch);

        if ($responseCode !== 200)
        {
            throw new InternalErrorException('Request to the Node server failed');
        }
    }
}
