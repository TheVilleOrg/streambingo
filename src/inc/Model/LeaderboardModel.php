<?php

/**
 * This file is part of StreamBingo.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 3 (GPL-3.0)
 *
 * For full license information, see the LICENSE file included with the source.
 */

declare (strict_types = 1);

namespace Bingo\Model;

use Bingo\Config;

/**
 * Represents a leaderboard entry of a Bingo player.
 */
class LeaderboardModel extends Model
{
    /**
     * The unique identifier associated with the user that owns this record
     *
     * @var int
     */
    protected $userId;

    /**
     * The name of the user that owns this record
     *
     * @var string
     */
    protected $userName;

    /**
     * The score associated with this record
     *
     * @var int
     */
    protected $score;

    /**
     * @param int $userId The unique identifier associated with the user that owns this record
     * @param string $userName The name of the user that owns this record
     * @param int $score The score associated with this record
     */
    protected function __construct(int $userId, string $userName, int $score)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->score = $score;
    }

    /**
     * Gets the global leaderboard records from the database.
     *
     * @return \Bingo\Model\LeaderboardModel[] The records
     */
    public static function loadGlobalLeaderboard(): array
    {
        return self::loadRecords();
    }

    /**
     * Gets leaderboard records for a channel from the database.
     *
     * @param string $gameName The unique name identifying the game
     *
     * @return \Bingo\Model\LeaderboardModel[] The records
     */
    public static function loadChannelLeaderboard(string $gameName): array
    {
        return self::loadRecords($gameName);
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * @return int The unique identifier associated with the user that owns this record
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string The name of the user that owns this record
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @return int The score associated with this record
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * Gets leaderboard records from the database.
     *
     * @param string|null $gameName The unique name identifying the game, or null for all games
     *
     * @return \Bingo\Model\LeaderboardModel[] The records
     */
    protected static function loadRecords(string $gameName = null): array
    {
        $records = [];

        $userId = $userName = $score = null;

        $sql = 'SELECT s.userId, u.name, COUNT(1) AS score FROM stats s LEFT JOIN users u ON s.userId = u.id WHERE s.numPlayers >= ' . (int) Config::LEADERBOARD_MIN_PLAYERS;
        if ($gameName)
        {
            $sql .= ' AND s.gameName = ?';
        }

        $sql .= ' GROUP BY s.userId ORDER BY score DESC;';

        $stmt = self::db()->prepare($sql);
        if ($gameName)
        {
            $stmt->bind_param('s', $gameName);
        }

        $stmt->execute();
        $stmt->bind_result($userId, $userName, $score);
        while ($stmt->fetch())
        {
            $records[] = new self($userId, $userName, $score);
        }

        $stmt->close();

        return $records;
    }
}
