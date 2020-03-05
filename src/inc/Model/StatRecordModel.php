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

/**
 * Represents a stats record of a Bingo player.
 */
class StatRecordModel extends Model
{
    /**
     * The unique identifier associated with the user that owns this record
     *
     * @var int
     */
    protected $userId;

    /**
     * The unique name identifying the game associated with this record
     *
     * @var string
     */
    protected $gameName;

    /**
     * The number of players in the game associated with this record
     *
     * @var int
     */
    protected $numPlayers;

    /**
     * The list of numbers assigned to the grid of the card associated with this record
     *
     * @var int[]
     */
    protected $grid = [];

    /**
     * The list of indexes of the marked grid cells in the card associated with this record
     *
     * @var int[]
     */
    protected $marked = [];

    /**
     * The list of numbers that were called in the game associated with this record
     *
     * @var int[]
     */
    protected $called = [];

    /**
     * The Unix timestamp when this record was created
     *
     * @var int
     */
    protected $time;

    /**
     * @param int $userId The unique identifier associated with the user that owns this record
     * @param string $gameName The unique name identifying the game associated with this record
     * @param int $numPlayers The number of players in the game associated with this record
     * @param array $grid The list of numbers assigned to the grid of the card associated with this record
     * @param array $marked The list of indexes of the marked grid cells in the card associated with this record
     * @param array $called The list of numbers that were called in the game associated with this record
     * @param int $time The Unix timestamp when this record was created
     */
    protected function __construct(int $userId, string $gameName, int $numPlayers, array $grid, array $marked, array $called, int $time)
    {
        $this->userId = $userId;
        $this->gameName = $gameName;
        $this->numPlayers = $numPlayers;
        $this->grid = $grid;
        $this->marked = $marked;
        $this->called = $called;
        $this->time = $time;
    }

    /**
     * Gets the stats records for a user.
     *
     * @param int $userId The unique identifier associated with the user
     *
     * @return \Bingo\Model\StatRecordModel[] The records
     */
    public static function loadPlayerStats(int $userId): array
    {
        return self::load($userId);
    }

    /**
     * Gets the stats records for a channel.
     *
     * @param string $gameName The unique name identifying the game
     *
     * @return \Bingo\Model\StatRecordModel[] The records
     */
    public static function loadChannelStats(string $gameName): array
    {
        return self::load(null, $gameName);
    }

    /**
     * Gets the stats records for a user in a channel.
     *
     * @param int $userId The unique identifier associated with the user
     * @param string $gameName The unique name identifying the game
     *
     * @return \Bingo\Model\StatRecordModel[] The records
     */
    public static function loadPlayerChannelStats(int $userId, string $gameName): array
    {
        return self::load($userId, $gameName);
    }

    /**
     * Creates a stats record.
     *
     * @param int $userId The unique identifier associated with the user that owns this record
     * @param string $gameName The unique name identifying the game associated with this record
     * @param int $numPlayers The number of players in the game associated with this record
     * @param array $grid The list of numbers assigned to the grid of the card associated with this record
     * @param array $marked The list of indexes of the marked grid cells in the card associated with this record
     * @param array $called The list of numbers that were called in the game associated with this record
     *
     * @return \Bingo\Model\StatRecordModel The record
     */
    public static function createRecord(int $userId, string $gameName, int $numPlayers, array $grid, array $marked, array $called): StatRecordModel
    {
        return new self($userId, $gameName, $numPlayers, $grid, $marked, $called, \time());
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        if ($this->id !== 0)
        {
            return false;
        }

        $userId = $this->getUserId();
        $gameName = $this->getGameName();
        $numPlayers = $this->getNumPlayers();
        $grid = \implode(',', $this->getGrid());
        $marked = \implode(',', $this->getMarked());
        $called = \implode(',', $this->getCalled());

        $stmt = self::db()->prepare('INSERT INTO stats (userId, gameName, numPlayers, grid, marked, called) VALUES (?, ?, ?, ?, ?, ?);');
        $stmt->bind_param('isisss', $userId, $gameName, $numPlayers, $grid, $marked, $called);
        $result = $stmt->execute();
        $stmt->close();

        if ($result)
        {
            $this->id = self::db()->insert_id;

            return true;
        }

        return false;
    }

    /**
     * @return int The unique identifier associated with the user that owns this record
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string The unique name identifying the game associated with this record
     */
    public function getGameName(): string
    {
        return $this->gameName;
    }

    /**
     * @return int The number of players in the game associated with this record
     */
    public function getNumPlayers(): int
    {
        return $this->numPlayers;
    }

    /**
     * @return array The list of numbers assigned to the grid of the card associated with this record
     */
    public function getGrid(): array
    {
        return $this->grid;
    }

    /**
     * @return array The list of indexes of the marked grid cells in the card associated with this record
     */
    public function getMarked(): array
    {
        return $this->marked;
    }

    /**
     * @return array The list of numbers that were called in the game associated with this record
     */
    public function getCalled(): array
    {
        return $this->called;
    }

    /**
     * @return int The Unix timestamp when this record was created
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * Gets stats records from the database.
     *
     * @param int|null $userId The unique identifier associated with the user, or null for all users
     * @param string|null $gameName The unique name identifying the game, or null for all games
     *
     * @return \Bingo\Model\StatRecordModel[] The records
     */
    protected static function load(int $userId = null, string $gameName = null): array
    {
        $stats = [];

        $id = $userId = $gameName = $numPlayers = $grid = $marked = $called = $time = null;

        $sql = 'SELECT id, userId, gameName, numPlayers, grid, marked, called, UNIX_TIMESTAMP(time) FROM stats';
        if ($userId || $gameName)
        {
            $sql .= ' WHERE';
        }

        if ($userId)
        {
            $sql .= ' userId = ?';
        }

        if ($gameName)
        {
            if ($userId)
            {
                $sql .= ' AND ';
            }

            $sql .= ' gameName = ?';
        }

        $sql .= ' ORDER BY time DESC;';

        $stmt = self::db()->prepare($sql);
        if ($userId && $gameName)
        {
            $stmt->bind_param('is', $userId, $gameName);
        }
        elseif ($userId)
        {
            $stmt->bind_param('i', $userId);
        }
        elseif ($gameName)
        {
            $stmt->bind_param('s', $gameName);
        }

        $stmt->execute();
        $stmt->bind_result($id, $userId, $gameName, $numPlayers, $grid, $marked, $called, $time);
        while ($stmt->fetch())
        {
            $grid = !empty($grid) ? \array_map('intval', \explode(',', $grid)) : [];
            $marked = !empty($marked) ? \array_map('intval', \explode(',', $marked)) : [];
            $called = !empty($called) ? \array_map('intval', \explode(',', $called)) : [];

            $stat = new self($userId, $gameName, $numPlayers, $grid, $marked, $called, $time);
            $stat->id = $id;

            $stats[] = $stat;
        }

        $stmt->close();

        return $stats;
    }
}
