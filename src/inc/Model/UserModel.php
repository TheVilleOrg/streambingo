<?php

declare (strict_types = 1);

namespace Bingo\Model;

use Bingo\Config;

/**
 * Represents a user.
 */
class UserModel extends Model
{
    /**
     * The name of this user
     *
     * @var string
     */
    protected $name = '';

    /**
     * The secret game token for this user
     *
     * @var string
     */
    protected $gameToken;

    /**
     * The Twitch user identifier for this user
     *
     * @var int
     */
    protected $twitchId;

    /**
     * The Twitch access token for this user
     *
     * @var string|null
     */
    protected $accessToken = null;

    /**
     * The Twitch refresh token for this user
     *
     * @var string|null
     */
    protected $refreshToken = null;

    /**
     * Whether this user is authorized to host games
     *
     * @var bool
     */
    protected $host = false;

    /**
     * The Unix timestamp when this user was created
     *
     * @var int
     */
    protected $created;

    /**
     * @param string $gameToken The secret game token for the user
     */
    protected function __construct(string $gameToken)
    {
        $this->gameToken = $gameToken;
    }

    /**
     * Loads a user from the database based on the unique identifier associated with the user.
     *
     * @param int $userId The unique identifier associated with the user
     *
     * @return \Bingo\Model\UserModel|null The user, or null if the user does not exist
     */
    public static function loadUserFromId(int $userId): ?UserModel
    {
        return self::loadUser($userId, false);
    }

    /**
     * Loads a user from the database based on the Twitch identifier associated with the user.
     *
     * @param int $twitchId The Twitch identifier associated with the user
     *
     * @return \Bingo\Model\UserModel|null The user, or null if the user does not exist
     */
    public static function loadUserFromTwitchId(int $twitchId): ?UserModel
    {
        return self::loadUser($twitchId, true);
    }

    /**
     * Loads a user from the database based on their secret game token.
     *
     * @param string $gameToken The secret game token of the user
     *
     * @return \Bingo\Model\UserModel|null The user, or null if the user does not exist
     */
    public static function loadUserFromGameToken(string $gameToken): ?UserModel
    {
        return self::loadUser(0, false, $gameToken);
    }

    /**
     * Creates a new user based on a Twitch access token.
     *
     * @param string $accessToken The Twitch access token for the user
     * @param string|null $refreshToken The Twitch refresh token for the user, or null if one is not provided
     *
     * @return \Bingo\Model\UserModel The user
     */
    public static function createUserFromToken(string $accessToken, string $refreshToken = null): UserModel
    {
        $gameToken = self::generateGameToken();

        $user = new self($gameToken);
        $user->accessToken = $accessToken;
        $user->refreshToken = $refreshToken;
        $user->created = \time();

        return $user;
    }

    /**
     * Creates a new user based on a Twitch identifier.
     *
     * @param string $twitchId The Twitch identifier of the user
     * @param string $name The Twitch login name of the user
     *
     * @return \Bingo\Model\UserModel The user
     */
    public static function createUserFromTwitchId(int $twitchId, string $name = ''): UserModel
    {
        $gameToken = self::generateGameToken();

        $user = new self($gameToken);
        $user->name = $name;
        $user->twitchId = $twitchId;
        $user->created = \time();

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        $name = $this->name;
        $gameToken = $this->gameToken;
        $twitchId = $this->twitchId;
        $accessToken = $this->accessToken;
        $refreshToken = $this->refreshToken;

        $stmt = self::db()->prepare('INSERT INTO users (name, gameToken, twitchId, accessToken, refreshToken) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = ?, accessToken = ?, refreshToken = ?;');
        $stmt->bind_param('ssisssss', $name, $gameToken, $twitchId, $accessToken, $refreshToken, $name, $accessToken, $refreshToken);
        $result = $stmt->execute();
        $stmt->close();

        if ($result)
        {
            if ($this->id === 0)
            {
                $this->id = self::db()->insert_id;
            }
        }

        return $result;
    }

    /**
     * @return string The name of this user
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name The name of this user
     *
     * @return \Bingo\Model\UserModel This object
     */
    public function setName(string $name): UserModel
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string The secret game token for this user
     */
    public function getGameToken(): string
    {
        return $this->gameToken;
    }

    /**
     * Replaces the secret game token for this user with a newly generated one.
     */
    public function invalidateGameToken(): void
    {
        $this->gameToken = self::generateGameToken();
    }

    /**
     * @return int The Twitch user identifier for this user
     */
    public function getTwitchId(): int
    {
        return $this->twitchId;
    }

    /**
     * @param int $twitchId The Twitch user identifier for this user
     *
     * @return \Bingo\Model\UserModel This object
     */
    public function setTwitchId(int $twitchId): UserModel
    {
        $this->twitchId = $twitchId;

        return $this;
    }

    /**
     * @return string The Twitch access token for this user
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string|null The Twitch refresh token for this user, or null if one is not available
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Sets the Twitch OAuth2 tokens for this user.
     *
     * @param string|null $accessToken The Twitch access token for this user
     * @param string|null $refreshToken The Twitch refresh token for this user, or null if one is not provided
     *
     * @return \Bingo\Model\UserModel This object
     */
    public function setTokens(string $accessToken = null, string $refreshToken = null): UserModel
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return bool True if this user is authorized to host games, false otherwise
     */
    public function getHost(): bool
    {
        return $this->host;
    }

    /**
     * @return int The Unix timestamp when this user was created
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * Loads a user from the database.
     *
     * @param string $ident The unique identifier associated with the game
     * @param bool $useTwitch True if $ident is a Twitch identifier, false if it is a unique identifier
     * @param string|null $gameToken The secret game token of the user
     *
     * @return \Bingo\Model\UserModel|null The user, or null if the user does not exist
     */
    protected static function loadUser(int $ident, bool $useTwitch, string $gameToken = null): ?UserModel
    {
        $user = $userId = $name = $twitchId = $accessToken = $refreshToken = $host = $created = null;

        $sql = 'SELECT id, name, gameToken, twitchId, accessToken, refreshToken, host, UNIX_TIMESTAMP(created) FROM users ';
        if ($useTwitch)
        {
            $sql .= 'WHERE twitchId = ?;';
        }
        elseif ($gameToken)
        {
            $sql .= 'WHERE gameToken = ?;';
        }
        else
        {
            $sql .= 'WHERE id = ?;';
        }


        $stmt = self::db()->prepare($sql);
        if ($gameToken && !$useTwitch)
        {
            $stmt->bind_param('s', $gameToken);
        }
        else
        {
            $stmt->bind_param('i', $ident);
        }

        $stmt->execute();
        $stmt->bind_result($userId, $name, $gameToken, $twitchId, $accessToken, $refreshToken, $host, $created);
        if ($stmt->fetch())
        {
            $user = new self($gameToken);
            $user->id = $userId;
            $user->name = $name;
            $user->twitchId = $twitchId;
            $user->accessToken = $accessToken;
            $user->refreshToken = $refreshToken;
            $user->host = (bool) $host;
            $user->created = $created;
        }

        $stmt->close();

        return $user;
    }

    /**
     * Generates a random unique token.
     *
     * @return string The token
     */
    protected static function generateGameToken(): string
    {
        return \base_convert((\microtime(true) * 1000), 10, 16) . \md5((string)\mt_rand());
    }
}
