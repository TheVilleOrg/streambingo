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
     * The name of the user associated with the winning card, or null if there is no winner
     *
     * @var string|null
     */
    protected $winnerName = null;

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
     * Gets all the active games.
     *
     * @return \Bingo\Model\GameMetaModel[] An array of games' metadata
     */
    public static function loadActiveGames(): array
    {
        return self::loadGames(null, null, true);
    }

    /**
     * Gets a game based on the unique identifier associated with the game.
     *
     * @param int $gameId The unique identifier associated with the game
     *
     * @return \Bingo\Model\GameMetaModel The games' metadata, or null if the game does not exist
     */
    public static function loadGameFromId(int $gameId): ?GameMetaModel
    {
        $games = self::loadGames($gameId);
        return $games[0] ?? null;
    }

    /**
     * Gets a game based on the unique name used to identify the game.
     *
     * @param string $gameName The unique name used to identify the game
     *
     * @return \Bingo\Model\GameMetaModel The games' metadata, or null if the game does not exist
     */
    public static function loadGameFromName(string $gameName): ?GameMetaModel
    {
        $games = self::loadGames(null, $gameName);
        return $games[0] ?? null;
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
     * @return string|null The name of the user associated with the winning card, or null if there is no winner
     */
    public function getWinnerName(): ?string
    {
        return $this->winnerName;
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

    /**
     * Loads games from the database.
     *
     * @param int $gameId The unique identifier associated with the game
     * @param string $gameName The unique name identifying the game
     * @param bool $active True to only load active games, false to load all games
     *
     * @return \Bingo\Model\GameMetaModel[] An array of games' metadata
     */
    protected static function loadGames(int $gameId = null, string $gameName = null, bool $active = false): array
    {
        $games = [];

        $userId = $ended = $winner = $winnerName = $created = $updated = $numCards = null;

        $sql = 'SELECT id, userId, gameName, ended, winner, winnerName, UNIX_TIMESTAMP(created), UNIX_TIMESTAMP(updated), (SELECT COUNT(1) FROM cards WHERE gameId = games.id) FROM games ';
        if ($gameId)
        {
            $sql .= 'WHERE id = ? ';
        }
        elseif ($gameName)
        {
            $sql .= 'WHERE gameName = ? ';
        }

        if ($active)
        {
            $sql .= ($gameId || $gameName) ? 'AND ' : 'WHERE ';
            $sql .= 'updated > NOW() - INTERVAL 5 MINUTE ';
        }

        $sql .= 'ORDER BY created ASC;';

        $stmt = self::db()->prepare($sql);
        if ($gameId)
        {
            $stmt->bind_param('i', $gameId);
        }
        elseif ($gameName)
        {
            $stmt->bind_param('s', $gameName);
        }

        $stmt->execute();
        $stmt->bind_result($gameId, $userId, $gameName, $ended, $winner, $winnerName, $created, $ended, $numCards);
        while ($stmt->fetch())
        {
            $game = new self($gameId, $userId, $gameName);
            $game->ended = (bool) $ended;
            $game->winner = $winner;
            $game->winnerName = $winnerName;
            $game->created = $created;
            $game->updated = $updated;
            $game->numCards = $numCards;

            $games[] = $game;
        }

        $stmt->close();

        return $games;
    }
}
