<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\architect;

use Pixie\Exception;
use Pixie\Connection;
use felicity\config\Config;
use felicity\architect\services\Uid;
use felicity\architect\services\QueryBuilder;
use felicity\architect\services\SchemaBuilder;

/**
 * Class Architect
 */
class Architect
{
    /** @var Architect $instance */
    public static $instance;

    /** @var Connection $connection */
    public $connection;

    /**
     * Architect constructor
     * @param array $overrideConfig
     */
    public function __construct(array $overrideConfig = [])
    {
        $driver = Config::get('felicity.architect.driver', 'mysql');

        $config = [
            'driver' => $driver,
            'host' => Config::get('felicity.architect.host', 'localhost'),
            'database' => Config::get('felicity.architect.database'),
            'username' => Config::get('felicity.architect.username'),
            'password' => Config::get('felicity.architect.password'),
            'charset' => Config::get('felicity.architect.charset', 'utf8mb4'),
            'collation' => Config::get('felicity.architect.collation', 'utf8mb4_general_ci'),
            'prefix' => Config::get('felicity.architect.prefix'),
        ];

        foreach ($overrideConfig as $key => $val) {
            if (array_key_exists($key, $config)) {
                $config[$key] = $val;
            }
        }

        $this->connection = new Connection($driver, $config);
    }

    /**
     * Gets the config class instance
     * @return Architect Singleton
     */
    public static function getInstance() : Architect
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Gets an instance of the query builder
     * @return QueryBuilder
     * @throws Exception
     */
    public static function get() : QueryBuilder
    {
        return self::getInstance()->getBuilder();
    }

    /**
     * Gets an instance of the query builder
     * @return QueryBuilder
     * @throws Exception
     */
    public function getBuilder() : QueryBuilder
    {
        return new QueryBuilder(
            new Uid(),
            $this->connection
        );
    }

    /**
     * Gets an instance of the schema builder
     * @return SchemaBuilder
     * @throws Exception
     */
    public static function schemaBuilder() : SchemaBuilder
    {
        return self::getInstance()->getSchemaBuilder();
    }

    /**
     * Gets an instance of the schema builder
     * @return SchemaBuilder
     * @throws Exception
     */
    public function getSchemaBuilder() : SchemaBuilder
    {
        return new SchemaBuilder(
            Config::getInstance(),
            $this->getBuilder(),
            Config::get('felicity.architect.prefix', '')
        );
    }
}
