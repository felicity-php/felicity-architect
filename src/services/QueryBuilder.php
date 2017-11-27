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
use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class QueryBuilder
 */
class QueryBuilder extends QueryBuilderHandler
{
    /** @var Uid $uidService */
    private $uidService;

    /**
     * QueryBuilder constructor
     * @param Uid $uidService
     * @param Connection $connection
     * @throws Exception
     */
    public function __construct(Uid $uidService, Connection $connection)
    {
        $this->uidService = $uidService;
        parent::__construct($connection);
    }

    /**
     * Gets a new query
     * @param Connection $connection
     * @return static
     * @throws Exception
     */
    public function newQuery(Connection $connection = null)
    {
        return new static($this->uidService, $connection);
    }

    /**
     * Sets the table(s) we're working with
     * @param string|array $tables Single table or multiple tables as an array
     *                             or as multiple parameters
     * @return static
     * @throws Exception
     */
    public function table($tables)
    {
        if (! \is_array($tables)) {
            // because a single table is converted to an array anyways,
            // this makes sense.
            $tables = \func_get_args();
        }

        $instance = new static($this->uidService, $this->connection);
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
     * @param $tableName
     * @return bool
     */
    public function tableExists($tableName) : bool
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
}
