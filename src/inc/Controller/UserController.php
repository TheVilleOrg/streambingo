<?php

declare (strict_types = 1);

namespace Bingo\Controller;

use Bingo\App;
use Bingo\Config;
use Bingo\Exception\InternalErrorException;
use Bingo\Model\UserModel;

/**
 * Provides an interface to the user functionality.
 */
class UserController
{
    /**
     * Tries to get the current authenticated user.
     *
     * @return \Bingo\Model\UserModel|null The currently authenticated user, or null if the user is not authenticated
     */
    public static function getCurrentUser(): ?UserModel
    {
        if (!\session_id())
        {
            \session_start();
        }

        if (isset($_SESSION['user_id']))
        {
            return UserModel::loadUser($_SESSION['user_id']);
        }

        $userId = \filter_input(INPUT_COOKIE, 'uid', FILTER_VALIDATE_INT);
        $accessToken = \filter_input(INPUT_COOKIE, 'access_token');
        if ($userId && $accessToken)
        {
            $user = UserModel::loadUser($userId);
            if (!$user || $accessToken !== $user->getAccessToken())
            {
                return null;
            }

            if (self::validateToken($user))
            {
                self::setCookies($user);
                $_SESSION['user_id'] = $user->getId();
                return $user;
            }
        }

        return null;
    }

    /**
     * Gets a user from the database.
     *
     * @param int $userId The unique identifier associated with the user
     *
     * @return \Bingo\Model\UserModel|null The user, or null if the user does not exist
     */
    public static function getUser(int $userId): ?UserModel
    {
        return UserModel::loadUser($userId);
    }

    /**
     * Gets an Twitch OAuth2 authorization URL.
     *
     * @param string $returnPath The URL path to which to return the user after authorization
     *
     * @return string The URL
     */
    public static function getAuthUrl(): string
    {
        if (!\session_id())
        {
            \session_start();
        }

        $_SESSION['return_url'] = Config::BASE_URL . Config::BASE_PATH . App::getRoute();
        $_SESSION['state'] = \md5((string) \mt_rand());

        $query = \http_build_query([
            'client_id'     => Config::TWITCH_APP_ID,
            'redirect_uri'  => Config::BASE_URL . Config::BASE_PATH . 'auth',
            'response_type' => 'code',
            'scope'         => 'chat:read',
            'state'         => $_SESSION['state'],
        ]);

        return 'https://id.twitch.tv/oauth2/authorize?' . $query;
    }

    /**
     * Processes an OAuth2 authorization code from Twitch.
     *
     * @param string $code The authorization code
     *
     * @return bool True if the authorization was successful, false otherwise
     *
     * @throws \Bingo\Exception\InternalErrorException
     */
    public static function processAuthCode(string $code): bool
    {
        $ch = \curl_init('https://id.twitch.tv/oauth2/token');
        if ($ch === false)
        {
            throw new InternalErrorException('Failed to connect to the Twitch API.');
        }

        \curl_setopt($ch, CURLOPT_POST, true);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'client_id'     => Config::TWITCH_APP_ID,
            'client_secret' => Config::TWITCH_APP_SECRET,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => Config::BASE_URL . Config::BASE_PATH . 'auth',
        ]);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = \curl_exec($ch);
        $responseCode = \curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        \curl_close($ch);

        if ($responseCode === 200)
        {
            $response = \json_decode($response, true);
            $user = UserModel::createUser($response['access_token'], $response['refresh_token']);

            if (self::validateToken($user))
            {
                self::setCookies($user);
                return true;
            }
        }

        return false;
    }

    /**
     * Validates an access token with the Twitch server.
     *
     * @param \Bingo\Model\UserModel $user The user to validate
     * @param bool $refresh True to attempt to refresh the access token, false otherwise
     *
     * @return bool True if the validation was successful, false otherwise
     *
     * @throws \Bingo\Exception\InternalErrorException
     */
    protected static function validateToken(UserModel $user, bool $refresh = true): bool
    {
        $ch = \curl_init('https://id.twitch.tv/oauth2/validate');
        if ($ch === false)
        {
            throw new InternalErrorException('Failed to connect to the Twitch API.');
        }

        \curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: OAuth ' . $user->getAccessToken(),
        ]);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = \curl_exec($ch);
        $responseCode = \curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        \curl_close($ch);

        if ($responseCode === 200)
        {
            $response = \json_decode($response, true);
            if (!$response)
            {
                return false;
            }

            if ($response['login'] !== $user->getName())
            {
                $user->setName($response['login']);
                $user->setTwitchId((int) $response['user_id']);
                $user->save();
            }

            return true;
        }
        elseif ($responseCode === 401 && $refresh && $user->getRefreshToken())
        {
            return self::refreshToken($user);
        }

        return false;
    }

    /**
     * Refreshes a Twitch access token.
     *
     * @param \Bingo\Model\UserModel $user The user to refresh
     *
     * @return bool True if the access token was refreshed, false otherwise
     */
    protected static function refreshToken(UserModel $user): bool
    {
        $ch = \curl_init('https://id.twitch.tv/oauth2/token');
        if ($ch === false)
        {
            throw new InternalErrorException('Failed to connect to the Twitch API.');
        }

        \curl_setopt($ch, CURLOPT_POST, true);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'client_id'     => Config::TWITCH_APP_ID,
            'client_secret' => Config::TWITCH_APP_SECRET,
            'refresh_token' => $user->getRefreshToken(),
            'grant_type'    => 'refresh_token',
        ]);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = \curl_exec($ch);
        $responseCode = \curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        \curl_close($ch);

        if ($responseCode === 200)
        {
            $response = \json_decode($response, true);
            $user->setTokens($response['access_token'], $response['refresh_token']);
            $user->save();

            return self::validateToken($user, false);
        }

        return false;
    }

    /**
     * Sets the cookies for a user.
     *
     * @param \Bingo\Model\UserModel $user The user for which to set cookies
     */
    protected static function setCookies(UserModel $user): void
    {
        $expire = time() + 2592000;
        \setcookie('uid', (string) $user->getId(), $expire);
        \setcookie('access_token', $user->getAccessToken(), $expire);
    }
}
