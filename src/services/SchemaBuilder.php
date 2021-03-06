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
 * Class SchemaBuilder
 */
class SchemaBuilder
{
    // use SchemaColumnTypes;
    // use SchemaForeignKeys;

    /*==========================================================================
        I tried to break this class apart into traits, and it worked great
        programmatically. But at the time of this writing, fucking PHPStorm
        completes chokes on traits. Unbelievable. PHPStorm is a love/hate
        relationship. Will someone please make a good and performant IDE? I'll
        switch to any platform for it. Fucking PHPStorm.
    /*========================================================================*/

    /*==========================================================================
        Main class
    /*========================================================================*/

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

    /** @var string $tablePrefix */
    private $tablePrefix;

    /** @var string $db */
    private $db;

    /** @var string $table */
    private $table;

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

        $indexes = [];

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

            if (isset($column['unique']) && $column['unique'] !== false) {
                $thisSql .= ' UNIQUE';
            }

            if (isset($column['default']) && $column['default'] !== false) {
                $thisSql .= " DEFAULT {$column['default']}";
            }

            $sql[] = $thisSql;

            if (isset($column['index']) && $column['index'] !== false) {
                $indexes[] = $colName;
            }
        }

        $sql[] = '`dateCreated` DATETIME';
        $sql[] = '`dateUpdated` DATETIME';
        $sql[] = '`uid` CHAR(24)';

        if ($this->db === 'mysql') {
            foreach ($indexes as $index) {
                $sql[] = "INDEX(`{$index}`)";
            }
        }

        foreach ($this->foreignKeys as $foreignKey) {
            $column = $foreignKey['column'] ?? false;
            $references = $foreignKey['references'] ?? false;
            $on = $foreignKey['on'] ?? false;
            $onUpdate = $foreignKey['onUpdate'] ?? false;
            $onDelete = $foreignKey['onDelete'] ?? false;

            if (! $column || ! $references || ! $on) {
                continue;
            }

            $on = "{$this->tablePrefix}{$on}";

            $thisSql = "FOREIGN KEY (`{$column}`) REFERENCES `{$on}`(`{$references}`)";

            if ($onUpdate) {
                $thisSql .= " ON UPDATE {$onUpdate}";
            }

            if ($onDelete) {
                $thisSql .= " ON DELETE {$onDelete}";
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
     * Gets the table drop SQL
     * @return string
     */
    public function dropSql() : string
    {
        return "DROP TABLE IF EXISTS {$this->table}";
    }

    /**
     * Gets the alter table sql
     * @return string
     */
    public function alterSql() : string
    {
        $sql = [];

        $indexes = [];

        $dropIndexes = [];

        foreach ($this->columns as $colName => $column) {
            $newName = $column['newName'] ?? $colName;
            $addFirst = $column['addFirst'] ?? false;
            $addAfter = $column['addAfter'] ?? false;

            $thisSql = "CHANGE `{$colName}` `{$newName}` {$column['type']}";

            if ($addFirst || $addAfter) {
                $thisSql = "ADD `{$newName}` {$column['type']}";
            }

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

            if (isset($column['unique']) && $column['unique'] !== false) {
                $thisSql .= ' UNIQUE';
            }

            if (isset($column['default']) && $column['default'] !== false) {
                $thisSql .= " DEFAULT {$column['default']}";
            }

            if ($addFirst) {
                $thisSql .= ' FIRST';
            } elseif ($addAfter) {
                $thisSql .= " AFTER {$addAfter}";
            }

            $sql[] = $thisSql;

            if (isset($column['index']) && $column['index'] !== false) {
                $indexes[] = $colName;
            } elseif (isset($column['dropIndex']) &&
                $column['dropIndex'] !== false
            ) {
                $dropIndexes[] = $colName;
            }
        }

        if ($this->db === 'mysql') {
            foreach ($indexes as $index) {
                $sql[] = " ADD INDEX(`{$index}`)";
            }

            foreach ($this->dropIndex as $index) {
                $sql[] = " DROP INDEX {$index}";
            }
        }

        foreach ($this->foreignKeys as $foreignKey) {
            $column = $foreignKey['column'] ?? false;
            $references = $foreignKey['references'] ?? false;
            $on = $foreignKey['on'] ?? false;
            $onUpdate = $foreignKey['onUpdate'] ?? false;
            $onDelete = $foreignKey['onDelete'] ?? false;

            if (! $column || ! $references || ! $on) {
                continue;
            }

            $on = "{$this->tablePrefix}{$on}";

            $thisSql = "ADD FOREIGN KEY (`{$column}`) REFERENCES `{$on}`(`{$references}`)";

            if ($onUpdate) {
                $thisSql .= " ON UPDATE {$onUpdate}";
            }

            if ($onDelete) {
                $thisSql .= " ON DELETE {$onDelete}";
            }

            $sql[] = $thisSql;
        }

        foreach ($this->dropForeign as $key) {
            $sql[] = "DROP FOREIGN KEY {$key}";
        }

        foreach ($this->dropColumn as $col) {
            $sql[] = "DROP {$col}";
        }

        $build = implode(",\n", $sql);

        $sql = "ALTER TABLE {$this->table} \n{$build}";

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
     * Drops the specified table
     */
    public function drop()
    {
        $this->queryBuilder->query($this->dropSql());
    }

    /**
     * Alters the specified table
     * @throws \Exception
     */
    public function alter()
    {
        if ($this->db !== 'mysql') {
            throw new \Exception(
                'Modifying an SQLite table is currently not supported because' .
                "\nyou have to re-define the table and create a new one. See" .
                "\nthis article on how to do it manually:\n" .
                'https://www.techonthenet.com/sqlite/tables/alter_table.php'
            );
        }

        $this->queryBuilder->query($this->alterSql());
    }

    /**
     * Renames the specified table
     * @param string $newName
     * @return self
     */
    public function rename(string $newName) : self
    {
        $newName = "{$this->tablePrefix}{$newName}";

        $sql = "RENAME TABLE `{$this->table}` TO `{$newName}`";

        if ($this->db !== 'mysql') {
            $sql = "ALTER TABLE `{$this->table}` RENAME TO `{$newName}`";
        }

        $this->queryBuilder->query($sql);

        $this->table = $newName;

        return $this;
    }


    /*==========================================================================
        SchemaColumnTypes (formerly a trait)
    /*========================================================================*/

    /** @var array $columns */
    private $columns = [];

    /** @var string $current */
    private $current;

    /** @var array $dropIndex */
    private $dropIndex = [];

    /** @var array $dropForeign */
    private $dropForeign = [];

    /** @var array $dropColumn */
    private $dropColumn = [];

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

    /**
     * Modifies the current column to be unique
     * @return self
     */
    public function unique() : self
    {
        if (! isset($this->columns[$this->current])) {
            return $this;
        }

        $this->columns[$this->current]['unique'] = true;

        return $this;
    }

    /**
     * Adds an index to the current column
     * @return self
     */
    public function index() : self
    {
        if (! isset($this->columns[$this->current])) {
            return $this;
        }

        $this->columns[$this->current]['index'] = true;

        return $this;
    }

    /**
     * Drops an index
     * @param string $columnName
     * @return self
     */
    public function dropIndex(string $columnName) : self
    {
        $this->dropIndex[] = $columnName;
        return $this;
    }

    /**
     * Drops a foreign key
     * @param string $columnName
     * @return self
     */
    public function dropForeign(string $columnName) : self
    {
        $this->dropForeign[] = $columnName;
        return $this;
    }

    /**
     * Drops a column
     * @param string $columnName
     * @return self
     */
    public function dropColumn(string $columnName) : self
    {
        $this->dropColumn[] = $columnName;
        return $this;
    }

    /**
     * Changes the column name
     * @param string $newName
     * @return self
     */
    public function changeName(string $newName) : self
    {
        if (! isset($this->columns[$this->current])) {
            return $this;
        }

        $this->columns[$this->current]['newName'] = $newName;

        return $this;
    }

    /**
     * Adds the current column as the first column on the table
     * @return self
     */
    public function addFirst() : self
    {
        if (! isset($this->columns[$this->current])) {
            return $this;
        }

        $this->columns[$this->current]['addFirst'] = true;

        return $this;
    }

    /**
     * Changes the column name
     * @param string $columnName
     * @return self
     */
    public function addAfter(string $columnName) : self
    {
        if (! isset($this->columns[$this->current])) {
            return $this;
        }

        $this->columns[$this->current]['addAfter'] = $columnName;

        return $this;
    }


    /*==========================================================================
        SchemaColumnTypes (formerly a trait)
    /*========================================================================*/

    /** @var array $foreignKeys */
    private $foreignKeys = [];

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
