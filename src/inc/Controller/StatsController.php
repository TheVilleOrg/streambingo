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

namespace Bingo\Controller;

use Bingo\Model\LeaderboardModel;

/**
 * Provides an interface to the stats functionality.
 */
class StatsController
{
    /**
     * Gets leaderboard records from the database.
     *
     * @param string|null $gameName The unique name identifying the game, or null for all games
     *
     * @return \Bingo\Model\LeaderboardModel[] The records
     */
    public static function getLeaderboard(string $gameName = null): array
    {
        return $gameName ? LeaderboardModel::loadChannelLeaderboard($gameName) : LeaderboardModel::loadGlobalLeaderboard();
    }
}
