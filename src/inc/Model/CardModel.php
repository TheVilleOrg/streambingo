<?php

declare (strict_types = 1);

namespace Bingo\Model;

use Bingo\Exception\GameException;

/**
 * Represents a Bingo card.
 */
class CardModel extends Model
{
    /**
     * The list of winning patterns a card can have.
     */
    const WINPATTERNS = [
        [0, 6, 12, 18, 24],
        [20, 16, 12, 8, 4],
        [0, 5, 10, 15, 20],
        [1, 6, 11, 16, 21],
        [2, 7, 12, 17, 22],
        [3, 8, 13, 18, 23],
        [4, 9, 14, 19, 24],
        [0, 1, 2, 3, 4],
        [5, 6, 7, 8, 9],
        [10, 11, 12, 13, 14],
        [15, 16, 17, 18, 19],
        [20, 21, 22, 23, 24],
    ];

    /**
     * The unique identifier of the game associated with this card
     *
     * @var int
     */
    protected $gameId;

    /**
     * The unique identifier associated with the user that owns this card
     *
     * @var int
     */
    protected $userId;

    /**
     * The list of numbers assigned to this card's grid
     *
     * @var int[]
     */
    protected $grid = [];

    /**
     * The list of indexes of the marked grid cells
     * @var int[]
     */
    protected $marked = [];

    /**
     * The Unix timestamp when this card was created
     *
     * @var int
     */
    protected $created;

    /**
     * The Unix timestamp when this card was last updated
     *
     * @var int
     */
    protected $updated;

    /**
     * The type of game this card is associated with
     *
     * @var int
     */
    protected $gameType;

    /**
     * The name of the game associated with this card
     *
     * @var string
     */
    protected $gameName;

    /**
     * True if the game associated with this card has ended, false otherwise
     *
     * @var bool
     */
    protected $gameEnded = false;

    /**
     * The name of the winner of the game associated with this card, or null if there is no winner
     *
     * @var string|null
     */
    protected $gameWinner = null;

    /**
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param int $gameId The unique identifier of the game associated with the card
     */
    protected function __construct(int $userId, int $gameId)
    {
        $this->userId = $userId;
        $this->gameId = $gameId;
    }

    /**
     * Loads a card from the database.
     *
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param int $gameId The unique identifier of the game associated with the card
     *
     * @return \Bingo\Model\CardModel|null The card, or null if the card does not exist
     */
    public static function loadCard(int $userId, int $gameId): ?CardModel
    {
        $cards = self::loadCards($userId, $gameId);
        return $cards[0] ?? null;
    }

    /**
     * Gets the cards for a user.
     *
     * @param int $userId The unique identifier associated with the user that owns the cards
     *
     * @return \Bingo\Model\CardModel[] The cards
     */
    public static function loadUserCards(int $userId): array
    {
        return self::loadCards($userId);
    }

    /**
     * Checks whether a card exists.
     *
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param int $gameId The unique identifier of the game associated with the card
     *
     * @return bool True if the card exists, false otherwise
     */
    public static function cardExists(int $userId, int $gameId): bool
    {
        $stmt = self::db()->prepare('SELECT 1 FROM cards WHERE userId = ? AND gameId = ?;');
        $stmt->bind_param('ii', $userId, $gameId);
        $stmt->execute();
        $result = $stmt->fetch() === true;
        $stmt->close();

        return $result;
    }

    /**
     * Creates a new card.
     *
     * @param int $userId The unique identifier associated with the user that owns the card
     * @param int $gameId The unique identifier of the game associated with the card
     *
     * @return \Bingo\Model\CardModel The card
     */
    public static function createCard(int $userId, int $gameId): CardModel
    {
        $card = new self($userId, $gameId);

        $grid = \array_chunk(\array_keys(\array_fill(1, 75, true)), 15);
        foreach ($grid as $column)
        {
            \shuffle($column);
            $column = \array_slice($column, 0, 5);
            $card->grid = \array_merge($card->grid, $column);
        }

        $card->created = $card->updated = time();

        return $card;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        $userId = $this->getUserId();
        $gameId = $this->getGameId();
        $grid = \implode(',', $this->getGrid());
        $marked = \implode(',', $this->getMarked());

        $stmt = self::db()->prepare('INSERT INTO cards (userId, gameId, grid, marked) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE grid = ?, marked = ?, updated = CURRENT_TIMESTAMP;');
        $stmt->bind_param('iissss', $userId, $gameId, $grid, $marked, $grid, $marked);
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
     * @return int The unique identifier of the game associated with this card
     */
    public function getGameId(): int
    {
        return $this->gameId;
    }

    /**
     * @return int The unique identifier associated with the user that owns this card
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int[] The list of numbers assigned to this card's grid
     */
    public function getGrid(): array
    {
        return $this->grid;
    }

    /**
     * @return int[] The list of indexes of the marked grid cells
     */
    public function getMarked(): array
    {
        return \array_keys($this->marked);
    }

    /**
     * Gets the marked status of a cell.
     *
     * @param int $i The cell index
     *
     * @return bool True if the cell is marked, false otherwise
     *
     * @throws \Bingo\Exception\GameException
     */
    public function getCellMarked(int $i): bool
    {
        if ($i < 0 || $i >= \count($this->grid))
        {
            throw new GameException('Attempted to get the status of an invalid cell');
        }

        return isset($this->marked[$i]);
    }

    /**
     * @return int The Unix timestamp when this card was created
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @return int The Unix timestamp when this card was created
     */
    public function getUpdated(): int
    {
        return $this->updated;
    }

    /**
     * @return int The type of game this card is associated with
     */
    public function getGameType(): int
    {
        return $this->gameType;
    }

    /**
     * @return string The name of the game associated with this card
     */
    public function getGameName(): string
    {
        return $this->gameName;
    }

    /**
     * @return bool True if the game associated with this card has ended, false otherwise
     */
    public function getGameEnded(): bool
    {
        return $this->gameEnded;
    }

    /**
     * @return string|null The name of the winner of the game associated with this card, or null if there is no winner
     */
    public function getGameWinner(): ?string
    {
        return $this->gameWinner;
    }

    /**
     * Marks a cell on the grid.
     *
     * @param int $i The cell index
     *
     * @return \Bingo\Model\CardModel This object
     *
     * @throws \Bingo\Exception\GameException
     */
    public function mark(int $i): CardModel
    {
        if ($i < 0 || $i >= \count($this->grid))
        {
            throw new GameException('Attempted to mark an invalid cell');
        }

        $this->marked[$i] = true;

        return $this;
    }

    /**
     * Unmarks a cell on the grid.
     *
     * @param int $i The cell index
     *
     * @return \Bingo\Model\CardModel This object
     *
     * @throws \Bingo\Exception\GameException
     */
    public function unmark(int $i): CardModel
    {
        if ($i < 0 || $i >= \count($this->grid))
        {
            throw new GameException('Attempted to unmark an invalid cell');
        }

        unset($this->marked[$i]);

        return $this;
    }

    /**
     * Checks the card against the win conditions.
     *
     * @param int[] $called The list of numbers that have been called in the game
     *
     * @return bool True if the card meets the win conditions, false otherwise
     */
    public function checkCard(array $called): bool
    {
        $called = \array_intersect($this->grid, $called);
        $marked = \array_intersect(\array_keys($this->marked), \array_keys($called));

        if ($this->gameType === GameModel::GAME_TYPE_FREE_LINE || $this->gameType === GameModel::GAME_TYPE_FREE_FILL)
        {
            $marked[] = 12;
            $marked = \array_unique($marked);
        }

        switch ($this->gameType)
        {
            case GameModel::GAME_TYPE_FREE_LINE:
            case GameModel::GAME_TYPE_LINE:
                foreach (self::WINPATTERNS as $pattern)
                {
                    $intersect = \array_intersect($pattern, $marked);
                    if (\count($intersect) === \count($pattern))
                    {
                        return true;
                    }
                }

                break;
            case GameModel::GAME_TYPE_FREE_FILL:
            case GameModel::GAME_TYPE_FILL:
                return \count($marked) === 25;
        }

        return false;
    }

    /**
     * Gets cards from the database.
     *
     * @param int $userId The unique identifier associated with the user that owns the cards
     * @param int|null $gameId The unique identifier of the game associated with the card, or null for all games
     *
     * @return \Bingo\Model\CardModel[] The cards
     */
    protected static function loadCards(int $userId, int $gameId = null): array
    {
        $cards = [];

        $card = $cardId = $grid = $marked = $created = $updated = $gameType = $gameName = $gameEnded = $gameWinner = null;

        $sql = 'SELECT c.id, c.gameId, c.grid, c.marked, UNIX_TIMESTAMP(c.created), UNIX_TIMESTAMP(c.updated), g.gameType, g.gameName, g.ended, g.winnerName FROM cards c LEFT JOIN games g ON c.gameId = g.id WHERE c.userId = ? ';
        if ($gameName)
        {
            $sql .= 'AND c.gameId = ? ';
        }

        $sql .= 'ORDER BY c.created DESC;';

        $stmt = self::db()->prepare($sql);
        if ($gameName)
        {
            $stmt->bind_param('ii', $userId, $gameId);
        }
        else
        {
            $stmt->bind_param('i', $userId);
        }

        $stmt->execute();
        $stmt->bind_result($cardId, $gameId, $grid, $marked, $created, $updated, $gameType, $gameName, $gameEnded, $gameWinner);
        while ($stmt->fetch())
        {
            $grid = \array_map('intval', \explode(',', $grid));
            $marked = $marked !== '' ? \array_map('intval', \explode(',', $marked)) : [];

            $card = new self($userId, $gameId);
            $card->id = $cardId;
            $card->grid = $grid;
            $card->marked = \array_fill_keys($marked, true);
            $card->created = $created;
            $card->updated = $updated;
            $card->gameType = $gameType;
            $card->gameName = $gameName;
            $card->gameEnded = (bool) $gameEnded;
            $card->gameWinner = $gameWinner;

            $cards[] = $card;
        }

        $stmt->close();

        return $cards;
    }
}
