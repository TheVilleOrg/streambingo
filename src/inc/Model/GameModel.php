<?php

declare (strict_types = 1);

namespace Bingo\Model;

use Bingo\Exception\GameException;

/**
 * Represents a Bingo game.
 */
class GameModel extends Model
{
    const GAME_TYPE_FREE_LINE = 0;
    const GAME_TYPE_LINE = 1;
    const GAME_TYPE_FREE_FILL = 2;
    const GAME_TYPE_FILL = 3;

    /**
     * The unique identifier associated with the user that owns this game
     *
     * @var int
     */
    protected $userId;

    /**
     * The unique name identifying this game
     *
     * @var string
     */
    protected $gameName;

    /**
     * The type of game
     *
     * @var int
     */
    protected $gameType = 0;

    /**
     * The list of numbers available to be called
     *
     * @var int[]
     */
    protected $balls = [];

    /**
     * The list of numbers that have been called
     *
     * @var int[]
     */
    protected $called = [];

    /**
     * Whether the game is in the ended state
     *
     * @var bool
     */
    protected $ended = false;

    /**
     * The unique identifier associated with the winning card, or null if there is no winner
     *
     * @var int|null
     */
    protected $winner = null;

    /**
     * The name of the winning user, or null if there is no winner
     *
     * @var string|null
     */
    protected $winnerName = null;

    /**
     * The Unix timestamp when this game was created
     *
     * @var int
     */
    protected $created;

    /**
     * The Unix timestamp when this game was last updated
     *
     * @var int
     */
    protected $updated;

    /**
     * The auto call interval in seconds
     *
     * @var int
     */
    protected $autoCall = 30;

    /**
     * The auto restart interval in seconds
     *
     * @var int
     */
    protected $autoRestart = 60;

    /**
     * The auto end interval in seconds
     *
     * @var int
     */
    protected $autoEnd = 60;

    /**
     * Whether text-to-speech is enabled
     *
     * @var bool
     */
    protected $tts = false;

    /**
     * The name of the text-to-speech voice to use
     *
     * @var string
     */
    protected $ttsVoice = '';

    /**
     * The background of the browser source
     *
     * @var string
     */
    protected $background = 'cycle';

    /**
     * @param int $userId The unique identifier associated with the user that owns the game
     * @param string $gameName The unique name identifying the game
     * @param int $gameType The type of game
     */
    protected function __construct(int $userId, string $gameName, int $gameType)
    {
        $this->userId = $userId;
        $this->gameName = $gameName;
        $this->gameType = \min(3, \max(0, $gameType));
    }

    /**
     * Loads a game from the database based on the unique name used to identify the game.
     *
     * @param string $gameName The unique name used to identify the game
     *
     * @return \Bingo\Model\GameModel|null The game, or null if the game does not exist
     */
    public static function loadGameFromName(string $gameName): ?GameModel
    {
        return self::loadGame($gameName, false);
    }

    /**
     * Loads a game from the database based on the secret game token associated with the game.
     *
     * @param string $gameToken The secret game token associated with the game
     *
     * @return \Bingo\Model\GameModel|null The game, or null if the game does not exist
     */
    public static function loadGameFromToken(string $gameToken): ?GameModel
    {
        return self::loadGame($gameToken, true);
    }

    /**
     * Creates a new game.
     *
     * @param int $userId The unique identifier associated with the user that owns the game
     * @param string $gameName The unique name identifying the game
     * @param int $gameType The type of game
     *
     * @return \Bingo\Model\GameModel The game
     */
    public static function createGame(int $userId, string $gameName, int $gameType = 0): GameModel
    {
        $game = new self($userId, $gameName, $gameType);

        $game->balls = \array_keys(\array_fill(1, 75, true));
        \shuffle($game->balls);
        $game->created = $game->updated = time();

        return $game;
    }

    /**
     * Deletes a game.
     *
     * @param string $gameName The unique name identifying the game
     */
    public static function deleteGame(string $gameName): void
    {
        $stmt = self::db()->prepare('DELETE FROM games WHERE gameName = ?;');
        $stmt->bind_param('s', $gameName);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Checks whether a game exists.
     *
     * @param string $gameName The unique name identifying the game
     *
     * @return bool True if the game exists, false otherwise
     */
    public static function gameExists(string $gameName): bool
    {
        $stmt = self::db()->prepare('SELECT 1 FROM games WHERE gameName = ?;');
        $stmt->bind_param('s', $gameName);
        $stmt->execute();
        $result = $stmt->fetch() === true;
        $stmt->close();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        $userId = $this->getUserId();
        $gameName = $this->getGameName();
        $gameType = $this->getGameType();
        $balls = \implode(',', $this->getBalls());
        $called = \implode(',', $this->getCalled());
        $ended = $this->getEnded();
        $winner = $this->getWinner();
        $winnerName = $this->getWinnerName();

        $stmt = self::db()->prepare('INSERT INTO games (userId, gameName, gameType, balls, called, ended, winner, winnerName) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE gameType = ?, balls = ?, called = ?, ended = ?, winner = ?, winnerName = ?, updated = CURRENT_TIMESTAMP;');
        $stmt->bind_param('isissiisissiis', $userId, $gameName, $gameType, $balls, $called, $ended, $winner, $winnerName, $gameType, $balls, $called, $ended, $winner, $winnerName);
        $result = $stmt->execute();
        $stmt->close();

        if ($result)
        {
            if ($this->id === 0)
            {
                $this->id = self::db()->insert_id;
            }

            $this->updated = time();

            return true;
        }

        return false;
    }

    /**
     * Saves the settings for the game.
     *
     * @return bool True if the save was successful, false otherwise
     */
    public function saveSettings(): bool
    {
        $gameName = $this->getGameName();
        $autoCall = $this->getAutoCall();
        $autoRestart = $this->getAutoRestart();
        $autoEnd = $this->getAutoEnd();
        $tts = $this->getTts();
        $ttsVoice = $this->getTtsVoice();
        $background = $this->getBackground();

        $stmt = self::db()->prepare('INSERT INTO game_settings (gameName, autoCall, autoRestart, autoEnd, tts, ttsVoice, background) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE autoCall = ?, autoRestart = ?, autoEnd = ?, tts = ?, ttsVoice = ?, background = ?;');
        $stmt->bind_param('siiiissiiiiss', $gameName, $autoCall, $autoRestart, $autoEnd, $tts, $ttsVoice, $background, $autoCall, $autoRestart, $autoEnd, $tts, $ttsVoice, $background);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * @return int The unique identifier associated with the user that owns this game
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string The unique name identifying this game
     */
    public function getGameName(): string
    {
        return $this->gameName;
    }

    /**
     * @return int[] The list of numbers available to be called
     */
    public function getBalls(): array
    {
        return $this->balls;
    }

    /**
     * @return int[] The list of numbers that have been called
     */
    public function getCalled(): array
    {
        return $this->called;
    }

    /**
     * @return bool True if the game is in the ended state, false otherwise
     */
    public function getEnded(): bool
    {
        return $this->ended;
    }

    /**
     * @param bool $ended True if the game is in the ended state, false otherwise
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setEnded(bool $ended): GameModel
    {
        $this->ended = $ended;

        return $this;
    }

    /**
     * @return int|null The unique identifier associated with the winning card, or null if there is no winner
     */
    public function getWinner(): ?int
    {
        return $this->winner;
    }

    /**
     * @param int $winner The unique identifier associated with the winning card, or null if there is no winner
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setWinner(int $winner = null): GameModel
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * @return string|null The name of the winning user, or null if there is no winner
     */
    public function getWinnerName(): ?string
    {
        return $this->winnerName;
    }

    /**
     * @param string|null $winnerName The name of the winning user, or null if there is no winner
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setWinnerName(string $winnerName = null): GameModel
    {
        $this->winnerName = $winnerName;

        return $this;
    }

    /**
     * @return int The Unix timestamp when this game was created
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @return int The Unix timestamp when this game was created
     */
    public function getUpdated(): int
    {
        return $this->updated;
    }

    /**
     * @return int The type of game
     */
    public function getGameType(): int
    {
        return $this->gameType;
    }

    /**
     * @return int The auto call interval in seconds
     */
    public function getAutoCall(): int
    {
        return $this->autoCall;
    }

    /**
     * @param int $autoCall The auto call interval in seconds
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setAutoCall(int $autoCall): GameModel
    {
        $this->autoCall = \min(600, \max(10, $autoCall));

        return $this;
    }

    /**
     * @return int The auto restart interval in seconds
     */
    public function getAutoRestart(): int
    {
        return $this->autoRestart;
    }

    /**
     * @param int $autoRestart The auto restart interval in seconds
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setAutoRestart(int $autoRestart): GameModel
    {
        $this->autoRestart = \min(600, \max(30, $autoRestart));

        return $this;
    }

    /**
     * @return int The auto end interval in seconds
     */
    public function getAutoEnd(): int
    {
        return $this->autoEnd;
    }

    /**
     * @param int $autoEnd The auto end interval in seconds
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setAutoEnd(int $autoEnd): GameModel
    {
        $this->autoEnd = \min(600, \max(30, $autoEnd));

        return $this;
    }

    /**
     * @return bool True if text-to-speech is enabled, false otherwise
     */
    public function getTts(): bool
    {
        return $this->tts;
    }

    /**
     * @param bool $tts True if text-to-speech is enabled, false otherwise
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setTts(bool $tts): GameModel
    {
        $this->tts = $tts;

        return $this;
    }

    /**
     * @return string The text-to-speech voice to use
     */
    public function getTtsVoice(): string
    {
        return $this->ttsVoice;
    }

    /**
     * @param string $ttsVoice The text-to-speech voice to use
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setTtsVoice(string $ttsVoice): GameModel
    {
        $this->ttsVoice = $ttsVoice;

        return $this;
    }

    /**
     * @return string The background of the browser source
     */
    public function getBackground(): string
    {
        return $this->background;
    }

    /**
     * @param string $background The background of the browser source
     *
     * @return \Bingo\Model\GameModel This object
     */
    public function setBackground(string $background): GameModel
    {
        $this->background = $background;

        return $this;
    }

    /**
     * Removes a number from the list of available numbers and adds it to the list of called numbers.
     *
     * @return int The number that was called
     *
     * @throws \Bingo\Exception\GameException
     */
    public function callNumber(): int
    {
        if ($this->ended)
        {
            throw new GameException('Attempted to call a number for an ended game');
        }

        if (empty($this->balls))
        {
            throw new GameException('Attempted to call a number but all numbers have been called');
        }

        $number = \array_pop($this->balls);
        $this->called[] = $number;

        return $number;
    }

    /**
     * Loads a game from the database.
     *
     * @param string $ident The unique identifier associated with the game
     * @param bool $useToken True if the unique identifier is a secret game token, false if it is a name
     *
     * @return \Bingo\Model\GameModel|null The game, or null if the game does not exist
     */
    protected static function loadGame(string $ident, bool $useToken): ?GameModel
    {
        $game = $gameId = $userId = $gameName = $gameType = $balls = $called = $ended = $winner = $winnerName = $created = $updated = $autoCall = $autoRestart = $autoEnd = $tts = $ttsVoice = $background = null;

        $sql = 'SELECT g.id, g.userId, g.gameName, g.gameType, g.balls, g.called, g.ended, g.winner, g.winnerName, UNIX_TIMESTAMP(g.created), UNIX_TIMESTAMP(g.updated), s.autoCall, s.autoRestart, s.autoEnd, s.tts, s.ttsVoice, s.background FROM games g LEFT JOIN game_settings s ON g.gameName = s.gameName WHERE ';
        $sql .= $useToken ? 'g.userId = (SELECT id FROM users WHERE gameToken = ?);' : 'g.gameName = ?;';

        $stmt = self::db()->prepare($sql);
        $stmt->bind_param('s', $ident);
        $stmt->execute();
        $stmt->bind_result($gameId, $userId, $gameName, $gameType, $balls, $called, $ended, $winner, $winnerName, $created, $updated, $autoCall, $autoRestart, $autoEnd, $tts, $ttsVoice, $background);
        if ($stmt->fetch())
        {
            $balls = !empty($balls) ? \array_map('intval', \explode(',', $balls)) : [];
            $called = !empty($called) ? \array_map('intval', \explode(',', $called)) : [];

            $game = new self($userId, $gameName, $gameType);
            $game->id = $gameId;
            $game->balls = $balls;
            $game->called = $called;
            $game->ended = (bool) $ended;
            $game->winner = $winner;
            $game->winnerName = $winnerName;
            $game->created = $created;
            $game->updated = $updated;
            $game->autoCall = $autoCall ?? 30;
            $game->autoRestart = $autoRestart ?? 60;
            $game->autoEnd = $autoEnd ?? 60;
            $game->tts = (bool) $tts;
            $game->ttsVoice = $ttsVoice ?? '';
            $game->background = $background ?? 'cycle';
        }

        $stmt->close();

        return $game;
    }
}
