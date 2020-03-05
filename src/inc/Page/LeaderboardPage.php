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

namespace Bingo\Page;

use Bingo\Controller\StatsController;

/**
 * Represents the handler for the leaderboard page.
 */
class LeaderboardPage extends Page
{
    /**
     * @inheritDoc
     */
    public function run(array $params): void
    {
        $data = [
            'records' => [],
        ];

        $records = StatsController::getLeaderboard($params[0] ?? null);
        foreach ($records as $record)
        {
            $data['records'][] = [
                'userName' => \htmlspecialchars($record->getUserName()),
                'score'    => $record->getScore(),
            ];
        }

        $this->showTemplate('leaderboard', $data);
    }
}
