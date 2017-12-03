<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\architect\services;

use DateTime;
use Pixie\Exception;
use Pixie\Connection;
use felicity\logging\Logger;
use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class QueryBuilder
 */
class QueryBuilder extends QueryBuilderHandler
{
    /** @var Uid $uidService */
    private $uidService;

    /** @var Logger $logger */
    private $logger;

    /**
     * QueryBuilder constructor
     * @param Uid $uidService
     * @param Logger $logger
     * @param Connection $connection
     * @throws Exception
     */
    public function __construct(
        Uid $uidService,
        Logger $logger,
        Connection $connection
    ) {
        $this->uidService = $uidService;
        $this->logger = $logger;
        parent::__construct($connection);
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return array PDOStatement and execution time as float
     */
    public function statement($sql, $bindings = []) : array
    {
        $start = microtime(true);

        $this->logger->addLog(
            "Architect running query at {$start}: {$this->interpolateQuery($sql, $bindings)}",
            Logger::LEVEL_INFO,
            'felicityArchitect'
        );

        $return = parent::statement($sql, $bindings);

        $end = microtime(true);

        $time = $end - $start . 's';

        $this->logger->addLog(
            "Architect query ended at {$end}. Time: {$time}",
            Logger::LEVEL_INFO,
            'felicityArchitect'
        );

        return $return;
    }

    /**
     * Gets a new query
     * @param Connection $connection
     * @return static
     */
    public function newQuery(Connection $connection = null)
    {
        return new static($this->uidService, $this->logger, $connection);
    }

    /**
     * Sets the table(s) we're working with
     * @param string|array $tables Single table or multiple tables as an array
     *                             or as multiple parameters
     * @return static
     */
    public function table($tables)
    {
        if (! \is_array($tables)) {
            // because a single table is converted to an array anyways,
            // this makes sense.
            $tables = \func_get_args();
        }

        $instance = new static($this->uidService, $this->logger, $this->connection);
        $tables = $this->addTablePrefix($tables, false);
        $instance->addStatement('tables', $tables);
        return $instance;
    }

    /**
     * Inserts specified data
     * @param array $data
     * @param bool $includeAuditData
     * @return array|string
     */
    public function insert($data, bool $includeAuditData = true)
    {
        if ($includeAuditData) {
            $dateTime = new DateTime();
            $dateFormat = $dateTime->format('Y-m-d h:i:s');
            $data['dateCreated'] = $dateFormat;
            $data['dateUpdated'] = $dateFormat;
            $data['uid'] = $this->uidService->generateUid();
        }

        return parent::insert($data);
    }

    /**
     * Inserts specified data
     * @param array $data
     * @param bool $includeAuditData
     * @return array|string
     */
    public function update($data, bool $includeAuditData = true)
    {
        if ($includeAuditData) {
            $dateTime = new DateTime();
            $dateFormat = $dateTime->format('Y-m-d h:i:s');
            $data['dateUpdated'] = $dateFormat;
        }

        return parent::update($data);
    }

    /**
     * Checks if a table exists
     * @param string $tableName
     * @return bool
     */
    public function tableExists(string $tableName) : bool
    {
        if ($this->tablePrefix) {
            $tableName = "{$this->tablePrefix}{$tableName}";
        }

        $sql = "SHOW TABLES LIKE '{$tableName}'";

        if ($this->adapter !== 'mysql') {
            $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name='{$tableName}'";
        }

        return \count($this->query($sql)->get()) > 0;
    }

    /**
     * Checks if a column exists on a table
     * @param string $colName
     * @param string $tableName
     * @return bool
     */
    public function columnExists(string $colName, string $tableName) : bool
    {
        if ($this->tablePrefix) {
            $tableName = "{$this->tablePrefix}{$tableName}";
        }

        $sql = "SHOW COLUMNS FROM `{$tableName}` LIKE '{$colName}'";

        if ($this->adapter !== 'mysql') {
            $sql = "PRAGMA table_info('{$tableName}')";
            $colExists = false;
            foreach ($this->query($sql)->get() as $item) {
                if ($item->name !== $colName) {
                    continue;
                }
                $colExists = true;
                break;
            }
            return $colExists;
        }

        return \count($this->query($sql)->get()) > 0;
    }

    /**
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from
     * $params are are in the same order as specified in $query
     * @param string $query The sql query with parameter placeholders
     * @param array $params The array of substitution parameters
     * @return string The interpolated query
     */
    private function interpolateQuery(string $query, array $params)
    {
        $keys = [];

        # Build a regular expression for each parameter
        foreach (array_keys($params) as $key) {
            if (\is_string($key)) {
                $keys[] = '/:'.$key.'/';
                continue;
            }

            $keys[] = '/[?]/';
        }

        $query = preg_replace($keys, $params, $query, 1, $count);

        return $query;
    }
}
