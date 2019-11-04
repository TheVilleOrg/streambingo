<?php

declare (strict_types = 1);

namespace Bingo\Model;

use Bingo\Config;

/**
 * Represents a data model for interaction with the database.
 */
abstract class Model
{
    /**
     * The object representing the database connection
     *
     * @var \mysqli
     */
    private static $db;

    /**
     * The unique identifier associated with this item
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Saves this item to the database.
     *
     * @return bool True if the save was successful, false otherwise
     */
    abstract public function save(): bool;

    /**
     * @return int The unique identifier associated with this item
     */
    final public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \mysqli The object representing the database connection
     */
    protected static function db(): \mysqli
    {
        if (!self::$db)
        {
            self::$db = new \mysqli(Config::DBHOST, Config::DBUSER, Config::DBPASS, Config::DBNAME);
        }

        return self::$db;
    }
}
