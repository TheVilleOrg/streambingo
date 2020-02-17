<?php

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
