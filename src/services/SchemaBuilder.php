<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\architect\services;

use felicity\config\Config;
use Pixie\QueryBuilder\QueryBuilderHandler as QueryBuilder;

/**
 * Class Architect
 */
class SchemaBuilder
{
    /** @var array $map */
    private static $map = [
        'pk' => [
            'mysql' => 'id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'sqlite' => 'id INTEGER PRIMARY KEY',
        ],
    ];

    /** @var Config $config */
    private $config;

    /** @var QueryBuilder $queryBuilder */
    private $queryBuilder;

    private $tablePrefix;

    /** @var string $db */
    private $db;

    /** @var string $table */
    private $table;

    /** @var array $columns */
    private $columns = [];

    /** @var string $current */
    private $current;

    /**
     * SchemaBuilder constructor
     * @param Config $config
     * @param QueryBuilder $queryBuilder
     * @param string $tablePrefix
     */
    public function __construct(
        Config $config,
        QueryBuilder $queryBuilder,
        string $tablePrefix = ''
    ) {
        $this->config = $config;
        $this->queryBuilder = $queryBuilder;
        $this->tablePrefix = $tablePrefix ?? '';
        $this->db = $this->queryBuilder->getConnection()->getAdapter();
    }

    /**
     * Sets the table name
     * @param string $name
     * @return self
     */
    public function table(string $name) : self
    {
        $this->table = "{$this->tablePrefix}{$name}";
        return $this;
    }

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

    /**
     * Gets the create table sql
     * @return string
     */
    public function createSql() : string
    {
        $db = $this->queryBuilder->getConnection()->getAdapter();

        $map = self::$map;

        $sql = [
            $map['pk'][$db]
        ];

        foreach ($this->columns as $colName => $column) {
            $thisSql = "`{$colName}` {$column['type']}";

            if (isset($column['colWidth']) &&
                $column['colWidth'] !== false &&
                $column['type'] !== 'INTEGER'
            ) {
                $thisSql .= "({$column['colWidth']})";
            }

            if (isset($column['unsigned']) && $column['unsigned'] !== false) {
                $thisSql .= ' UNSIGNED';
            }

            if (isset($column['notNull']) && $column['notNull'] !== false) {
                $thisSql .= ' NOT NULL';
            }

            if (isset($column['default']) && $column['default'] !== false) {
                $thisSql .= " DEFAULT {$column['default']}";
            }

            $sql[] = $thisSql;
        }

        $build = implode(",\n", $sql);

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (\n{$build}\n)";

        if ($this->db !== 'mysql') {
            return $sql;
        }

        $charset = $this->config->getItem(
            'felicity.architect.charset',
            'utf8mb4'
        );

        $collate = $this->config->getItem(
            'felicity.architect.collation',
            'utf8mb4_general_ci'
        );

        $sql .= " ENGINE=InnoDB DEFAULT CHARSET {$charset} COLLATE {$collate}";

        return $sql;
    }

    /**
     * Creates the defined table
     */
    public function create()
    {
        $this->queryBuilder->query($this->createSql());
    }

    /**
     * Gets the table drop SQL
     * @return string
     */
    public function dropSql() : string
    {
        return "DROP TABLE IF EXISTS {$this->table}";
    }

    /**
     * Drops the specified table
     */
    public function drop()
    {
        $this->queryBuilder->query($this->dropSql());
    }
}
