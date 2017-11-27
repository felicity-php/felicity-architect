<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\architect\traits;

/**
 * Trait SchemaColumnTypes
 */
trait SchemaColumnTypes
{
    /** @var array $columns */
    private $columns = [];

    /** @var string $current */
    private $current;

    /**
     * Adds a BIGINT column to the table
     * @param string $columnName
     * @param string $val
     * @return self
     */
    public function raw(string $columnName, string $val) : self
    {
        $this->columns[$columnName] = [
            'type' => $val,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a BIGINT column to the table
     * @param string $columnName
     * @return self
     */
    public function bigInteger(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => $this->db === 'mysql' ? 'BIGINT' : 'INTEGER',
            'colWidth' => 20,
            'notNull' => false,
            'unsigned' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a BLOB column to the table
     * @param string $columnName
     * @return self
     */
    public function binary(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'BLOB',
            'notNull' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a TINYINT column to the table
     * @param string $columnName
     * @return self
     */
    public function boolean(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => $this->db === 'mysql' ? 'TINYINT' : 'INTEGER',
            'colWidth' => 1,
            'notNull' => true,
            'default' => 0,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a CHAR column to the table
     * @param string $columnName
     * @return self
     */
    public function char(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'CHAR',
            'colWidth' => 1,
            'notNull' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a DATE column to the table
     * @param string $columnName
     * @return self
     */
    public function date(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'DATE',
            'notNull' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a DATETIME column to the table
     * @param string $columnName
     * @return self
     */
    public function dateTime(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'DATETIME',
            'notNull' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a DATETIME column to the table
     * @param string $columnName
     * @return self
     */
    public function float(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'FLOAT',
            'colWidth' => false,
            'notNull' => false,
            'unsigned' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a INT column to the table
     * @param string $columnName
     * @return self
     */
    public function integer(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => $this->db === 'mysql' ? 'INT' : 'INTEGER',
            'colWidth' => false,
            'notNull' => false,
            'unsigned' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a LONGTEXT column to the table
     * @param string $columnName
     * @return self
     */
    public function longtext(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'LONGTEXT',
            'notNull' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a MEDIUMINT column to the table
     * @param string $columnName
     * @return self
     */
    public function mediumInteger(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => $this->db === 'mysql' ? 'MEDIUMINT' : 'INTEGER',
            'colWidth' => false,
            'notNull' => false,
            'unsigned' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a MEDIUMTEXT column to the table
     * @param string $columnName
     * @return self
     */
    public function mediumText(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'MEDIUMTEXT',
            'notNull' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a SMALLINT column to the table
     * @param string $columnName
     * @return self
     */
    public function smallInteger(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => $this->db === 'mysql' ? 'SMALLINT' : 'INTEGER',
            'colWidth' => false,
            'notNull' => false,
            'unsigned' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a TINYINT column to the table
     * @param string $columnName
     * @return self
     */
    public function tinyInteger(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => $this->db === 'mysql' ? 'TINYINT' : 'INTEGER',
            'colWidth' => false,
            'notNull' => false,
            'unsigned' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a VARCHAR column to the table
     * @param string $columnName
     * @return self
     */
    public function string(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'VARCHAR',
            'colWidth' => 255,
            'notNull' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a TEXT column to the table
     * @param string $columnName
     * @return self
     */
    public function text(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'TEXT',
            'notNull' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a TIME column to the table
     * @param string $columnName
     * @return self
     */
    public function time(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'TIME',
            'notNull' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Adds a TIMESTAMP column to the table
     * @param string $columnName
     * @return self
     */
    public function timestamp(string $columnName) : self
    {
        $this->columns[$columnName] = [
            'type' => 'TIMESTAMP',
            'notNull' => false,
            'default' => false,
        ];

        $this->current = $columnName;

        return $this;
    }

    /**
     * Modifies the current column's colWidth
     * @param int $width
     * @return self
     */
    public function colWidth(int $width) : self
    {
        if (! isset($this->columns[$this->current]['colWidth'])) {
            return $this;
        }

        $this->columns[$this->current]['colWidth'] = $width;

        return $this;
    }

    /**
     * Modifies the current column's notNull property
     * @param bool $notNull
     * @return self
     */
    public function notNull(bool $notNull = true) : self
    {
        if (! isset($this->columns[$this->current]['notNull'])) {
            return $this;
        }

        $this->columns[$this->current]['notNull'] = $notNull;

        return $this;
    }

    /**
     * Modifies the current column's unsigned property
     * @param bool $unsigned
     * @return self
     */
    public function unsigned(bool $unsigned = true) : self
    {
        if (! isset($this->columns[$this->current]['unsigned'])) {
            return $this;
        }

        $this->columns[$this->current]['unsigned'] = $unsigned;

        return $this;
    }

    /**
     * Modifies the current column's default value
     * @param $val
     * @return self
     */
    public function default($val) : self
    {
        if (! isset($this->columns[$this->current]['default'])) {
            return $this;
        }

        $this->columns[$this->current]['default'] = $val;

        return $this;
    }
}
