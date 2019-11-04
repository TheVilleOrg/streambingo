<?php

declare (strict_types = 1);

namespace Bingo\Model;

/**
 * Represents the metadata of a Bingo game.
 */
class GameMetaModel extends Model
{
    /**
     * The unique identifier associated with the user that owns this game
     *
     * @var int
     */
    protected $userId;

    /**
     * The unique name identifying this game
     *
     * @var string
     */
    protected $gameName;

    /**
     * Whether the game is in the ended state
     *
     * @var bool
     */
    protected $ended = false;

    /**
     * The unique identifier associated with the winning card, or null if there is no winner
     *
     * @var int|null
     */
    protected $winner = null;

    /**
     * The Unix timestamp when this game was created
     *
     * @var int
     */
    protected $created;

    /**
     * The Unix timestamp when this game was last updated
     *
     * @var int
     */
    protected $updated;

    /**
     * The number of cards associated with this game.
     *
     * @var int
     */
    protected $numCards = 0;

    /**
     * @param int $gameId The unique identifier associated with the game
     * @param int $userId The unique identifier associated with the user that owns the game
     * @param string $gameName The unique name identifying the game
     */
    protected function __construct(int $gameId, int $userId, string $gameName)
    {
        $this->id = $gameId;
        $this->userId = $userId;
        $this->gameName = $gameName;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Gets all the games.
     *
     * @return \Bingo\Model\GameMetaModel[] An array of games' metadata
     */
    public static function getGames(): array
    {
        $games = [];

        $gameId = $userId = $gameName = $ended = $winner = $created = $updated = $numCards = null;

        $stmt = self::db()->prepare('SELECT id, userId, gameName, ended, winner, UNIX_TIMESTAMP(created), UNIX_TIMESTAMP(updated), (SELECT COUNT(1) FROM cards WHERE gameName = games.gameName) FROM games ORDER BY created ASC;');
        $stmt->execute();
        $stmt->bind_result($gameId, $userId, $gameName, $ended, $winner, $created, $updated, $numCards);
        while ($stmt->fetch())
        {
            $game = new self($gameId, $userId, $gameName);
            $game->ended = (bool) $ended;
            $game->winner = $winner;
            $game->created = $created;
            $game->updated = $updated;
            $game->numCards = $numCards;

            $games[] = $game;
        }

        $stmt->close();

        return $games;
    }

    /**
     * @return int The unique identifier associated with the user that owns this game
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string The unique name identifying this game
     */
    public function getGameName(): string
    {
        return $this->gameName;
    }

    /**
     * @return bool True if the game is in the ended state, false otherwise
     */
    public function getEnded(): bool
    {
        return $this->ended;
    }

    /**
     * @return int|null The unique identifier associated with the winning card, or null if there is no winner
     */
    public function getWinner(): ?int
    {
        return $this->winner;
    }

    /**
     * @return int The Unix timestamp when this game was created
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @return int The Unix timestamp when this game was created
     */
    public function getUpdated(): int
    {
        return $this->updated;
    }

    /**
     * @return int The number of cards associated with this game
     */
    public function getNumCards(): int
    {
        return $this->numCards;
    }
}
