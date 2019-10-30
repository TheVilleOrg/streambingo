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
	 * The Twitch access token for this user
	 *
	 * @var string
	 */
	protected $accessToken;

	/**
	 * The Twitch refresh token for this user
	 *
	 * @var string|null
	 */
	protected $refreshToken;

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
	 * @param int $userId The unique identifier associated with the user
	 * @param string $accessToken The Twitch access token for the user
	 * @param string|null $refreshToken The Twitch refresh token for the user, or null if one is not provided
	 */
	protected function __construct(int $userId, string $accessToken, string $refreshToken = null)
	{
		$this->id = $userId;
		$this->accessToken = $accessToken;
		$this->refreshToken = $refreshToken;
	}

	/**
	 * Loads a user from the database.
	 *
	 * @param int $userId The unique identifier associated with the user
	 *
	 * @return \Bingo\Model\UserModel|null The user, or null if the user does not exist
	 */
	public static function loadUser(int $userId): ?UserModel
	{
		$user = $name = $accessToken = $refreshToken = $host = $created = null;

		$stmt = self::db()->prepare('SELECT name, accessToken, refreshToken, host, UNIX_TIMESTAMP(created) FROM users WHERE id = ?;');
		$stmt->bind_param('i', $userId);
		$stmt->execute();
		$stmt->bind_result($name, $accessToken, $refreshToken, $host, $created);
		if ($stmt->fetch())
		{
			$user = new self($userId, $accessToken, $refreshToken);
			$user->name = $name;
			$user->host = (bool) $host;
			$user->created = $created;
		}

		$stmt->close();

		return $user;
	}

	/**
	 * Creates a new user.
	 *
	 * @param int $userId The unique identifier associated with the user
	 * @param string $name The name of the user
	 * @param string $accessToken The Twitch access token for the user
	 * @param string|null $refreshToken The Twitch refresh token for the user, or null if one is not provided
	 *
	 * @return \Bingo\Model\UserModel The user
	 */
	public static function createUser(int $userId, string $name, string $accessToken, string $refreshToken = null): UserModel
	{
		$user = new self($userId, $accessToken, $refreshToken);
		$user->name = $name;
		$user->created = time();

		return $user;
	}

	/**
	 * @inheritDoc
	 */
	public function save(): bool
	{
		$userId = $this->id;
		$name = $this->name;
		$accessToken = $this->accessToken;
		$refreshToken = $this->refreshToken;
		$host = $this->host;

		$stmt = self::db()->prepare('INSERT INTO users (id, name, accessToken, refreshToken, host) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = ?, accessToken = ?, refreshToken = ?, host = ?;');
		$stmt->bind_param('isssisssi', $userId, $name, $accessToken, $refreshToken, $host, $name, $accessToken, $refreshToken, $host);
		$result = $stmt->execute();
		$stmt->close();

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
	 * @param string $accessToken The Twitch access token for this user
	 * @param string|null $refreshToken The Twitch refresh token for this user, or null if one is not provided
	 *
	 * @return \Bingo\Model\UserModel This object
	 */
	public function setTokens(string $accessToken, string $refreshToken = null): UserModel
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
}
