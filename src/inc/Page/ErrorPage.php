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

/**
 * Represents the handler for the error page.
 */
class ErrorPage extends Page
{
    /**
     * @inheritDoc
     */
    protected function run(array $params): void
    {
        $this->showTemplate('error', $params);
    }
}
