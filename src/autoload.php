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

namespace Bingo;

spl_autoload_register(function ($className) {
    $path = \explode('\\', $className);
    $path[0] = 'inc';
    $filePath = \implode(DIRECTORY_SEPARATOR, $path) . '.php';
    if (\file_exists(__DIR__ . DIRECTORY_SEPARATOR . $filePath))
    {
        require_once $filePath;
    }
});
