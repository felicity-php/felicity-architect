<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\architect\services;

use felicity\config\Config;
use felicity\architect\traits\SchemaColumnTypes;
use felicity\architect\traits\SchemaForeignKeys;
use Pixie\QueryBuilder\QueryBuilderHandler as QueryBuilder;

/**
 * Class SchemaBuilder
 */
class SchemaBuilder
{
    use SchemaColumnTypes;
    use SchemaForeignKeys;

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

        $sql[] = '`dateCreated` DATETIME';
        $sql[] = '`dateUpdated` DATETIME';
        $sql[] = '`uid` CHAR(24)';

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
