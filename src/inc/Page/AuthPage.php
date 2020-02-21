<?php

declare (strict_types = 1);

namespace Bingo\Page;

use Bingo\Config;
use Bingo\Controller\UserController;
use Bingo\Exception\BadRequestException;
use Bingo\Exception\InternalErrorException;

/**
 * Represents the handler for the authorization page.
 */
class AuthPage extends Page
{
    /**
     * @inheritDoc
     *
     * @throws \Bingo\Exception\BadRequestException
     * @throws \Bingo\Exception\InternalErrorException
     */
    public function run(array $params): void
    {
        if ($params[0] ?? null === 'logout')
        {
            UserController::logoutUser();

            $url = \filter_input(INPUT_GET, 'return_url', FILTER_SANITIZE_URL);
            $url = empty($url) ? Config::BASE_PATH : $url;

            \header('Location: ' . $url);
            return;
        }

        if (!\filter_has_var(INPUT_GET, 'code'))
        {
            throw new BadRequestException('No `code` parameter present.');
        }

        if (\filter_input(INPUT_GET, 'state') !== $_SESSION['state'])
        {
            throw new BadRequestException('The `state` parameter does not match the state.');
        }

        if (UserController::processAuthCode(\filter_input(INPUT_GET, 'code')))
        {
            \header('Location: ' . $_SESSION['return_url']);
            return;
        }

        throw new InternalErrorException('Failed to authenticate with Twitch');
    }
}
