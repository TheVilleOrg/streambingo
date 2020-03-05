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

use Bingo\Controller\GameController;

/**
 * Represents the handler for the index page.
 */
class IndexPage extends Page
{
    /**
     * @inheritDoc
     */
    public function run(array $params): void
    {
        $data = [
            'games' => [],
        ];

        $games = GameController::getGameList();
        foreach ($games as $game)
        {
            $data['games'][] = [
                'name'     => $game->getGameName(),
                'numCards' => $game->getNumCards(),
            ];
        }

        $this->showTemplate('index', $data);
    }
}
