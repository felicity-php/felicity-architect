<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\architect\traits;

/**
 * Trait SchemaForeignKeys
 */
trait SchemaForeignKeys
{
    /** @var array $foreignKeys */
    private $foreignKeys = [];

    /** @var string $current */
    private $current;

    /**
     * Sets the column name of a foreign key constraint
     * @param string $columnName
     * @return self
     */
    public function foreign(string $columnName) : self
    {
        $this->foreignKeys[$columnName]['column'] = $columnName;

        $this->current = $columnName;

        return $this;
    }

    /**
     * Sets the foreign column name of a foreign key constraint
     * @param string $columnName
     * @return self
     */
    public function references(string $columnName) : self
    {
        if (! isset($this->foreignKeys[$this->current])) {
            return $this;
        }

        $this->foreignKeys[$this->current]['references'] = $columnName;

        return $this;
    }

    /**
     * Sets the foreign table name of a foreign key constraint
     * @param string $tableName
     * @return self
     */
    public function on(string $tableName) : self
    {
        if (! isset($this->foreignKeys[$this->current])) {
            return $this;
        }

        $this->foreignKeys[$this->current]['on'] = $tableName;

        return $this;
    }

    /**
     * Sets the foreign key constraint ON UPDATE
     * @param string $val
     * @return self
     */
    public function onUpdate(string $val) : self
    {
        if (! isset($this->foreignKeys[$this->current])) {
            return $this;
        }

        $this->foreignKeys[$this->current]['onUpdate'] = $val;

        return $this;
    }

    /**
     * Sets the foreign key constraint ON DELETE
     * @param string $val
     * @return self
     */
    public function onDelete(string $val) : self
    {
        if (! isset($this->foreignKeys[$this->current])) {
            return $this;
        }

        $this->foreignKeys[$this->current]['onDelete'] = $val;

        return $this;
    }
}
