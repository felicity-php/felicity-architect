<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace tests\services;

use felicity\config\Config;
use PHPUnit\Framework\TestCase;
use felicity\architect\Architect;

/**
 * Class SchemaBuilderTest
 */
class SchemaBuilderTest extends TestCase
{
    /**
     * Test architect on MySQL
     * @group mysql
     */
    public function testMySql()
    {
        Config::set('felicity.architect', [
            'database' => 'site',
            'username' => 'site',
            'password' => 'secret',
            'prefix' => 'test_'
        ]);

        Architect::schemaBuilder()->table('mySqlTest')->drop();

        Architect::schemaBuilder()->table('mySqlTest')
            ->bigInteger('testBigInt')->colWidth(30)->notNull()->unsigned()->default(10)
            ->binary('testBlob')
            ->boolean('boolTest')
            ->char('testChar')->notNull()->default("'a'")
            ->date('testDate')
            ->raw('rawTest', 'VARCHAR(30)')
            ->dateTime('testDateTime')
            ->float('testFloat')
            ->longtext('testLongText')
            ->mediumInteger('mediumIntegerTest')
            ->mediumText('mediumText')
            ->smallInteger('smallInt')
            ->tinyInteger('tinyInt')
            ->string('testString')
            ->time('testTime')
            ->timestamp('testTimestamp')
            ->create();

        self::assertEquals(
            'test_mySqlTest',
            Architect::get()->query('SHOW TABLES')->get()[0]->Tables_in_site
        );

        Architect::get()->table('mySqlTest')->insert([
            'testBigInt' => 1234,
            'testBlob' => 'blobTest',
            'boolTest' => 1,
            'testChar' => 'b',
            'testDate' => '2017-11-26',
            'rawTest' => 'testRaw',
            'testDateTime' => '2017-11-26 15:08:01',
            'testFloat' => 1.2,
            'testLongText' => 'longTextTest',
            'mediumIntegerTest' => 42,
            'mediumText' => 'textMediumTest',
            'smallInt' => 2,
            'tinyInt' => 1,
            'testString' => 'stringTest',
            'testTime' => '15:08:01',
            'testTimestamp' => '2017-11-26 15:08:01',
        ]);

        $row = Architect::get()->table('mySqlTest')
            ->where('testBigInt', 1234)
            ->first();

        $row = $row ?? new \stdClass();

        self::assertEquals(1, $row->id);
        self::assertEquals(1234, $row->testBigInt);
        self::assertEquals('blobTest', $row->testBlob);
        self::assertEquals(1, $row->boolTest);
        self::assertEquals('b', $row->testChar);
        self::assertEquals('2017-11-26', $row->testDate);
        self::assertEquals(1.2, $row->testFloat);
        self::assertEquals('longTextTest', $row->testLongText);
        self::assertEquals(42, $row->mediumIntegerTest);
        self::assertEquals('textMediumTest', $row->mediumText);
        self::assertEquals(2, $row->smallInt);
        self::assertEquals(1, $row->tinyInt);
        self::assertEquals('stringTest', $row->testString);
        self::assertEquals('2017-11-26 15:08:01', $row->testTimestamp);

        Architect::schemaBuilder()->table('mySqlTest')->drop();
    }

    /**
     * Test architect on MySQL
     * @group sqlite
     */
    public function testSqlite()
    {
        $sqliteDb = \dirname(__DIR__) . '/test.sqlite';

        Config::set('felicity.architect', [
            'driver' => 'sqlite',
            'database' => $sqliteDb,
            'prefix' => 'test_'
        ]);

        Architect::schemaBuilder()->table('mySqlTest')->drop();

        Architect::schemaBuilder()->table('mySqlTest')
            ->bigInteger('testBigInt')->colWidth(30)->notNull()->unsigned()->default(10)
            ->binary('testBlob')
            ->boolean('boolTest')
            ->char('testChar')->notNull()->default("'a'")
            ->date('testDate')
            ->raw('rawTest', 'VARCHAR(30)')
            ->dateTime('testDateTime')
            ->float('testFloat')
            ->longtext('testLongText')
            ->mediumInteger('mediumIntegerTest')
            ->mediumText('mediumText')
            ->smallInteger('smallInt')
            ->tinyInteger('tinyInt')
            ->string('testString')
            ->time('testTime')
            ->timestamp('testTimestamp')
            ->create();

        self::assertEquals(
            'test_mySqlTest',
            Architect::get()
                ->query("SELECT * FROM sqlite_master WHERE type='table'")
                ->get()[0]->name
        );

        Architect::get()->table('mySqlTest')->insert([
            'testBigInt' => 1234,
            'testBlob' => 'blobTest',
            'boolTest' => 1,
            'testChar' => 'b',
            'testDate' => '2017-11-26',
            'rawTest' => 'testRaw',
            'testDateTime' => '2017-11-26 15:08:01',
            'testFloat' => 1.2,
            'testLongText' => 'longTextTest',
            'mediumIntegerTest' => 42,
            'mediumText' => 'textMediumTest',
            'smallInt' => 2,
            'tinyInt' => 1,
            'testString' => 'stringTest',
            'testTime' => '15:08:01',
            'testTimestamp' => '2017-11-26 15:08:01',
        ]);

        $row = Architect::get()->table('mySqlTest')
            ->where('testBigInt', 1234)
            ->first();

        $row = $row ?? new \stdClass();

        self::assertEquals(1, $row->id);
        self::assertEquals(1234, $row->testBigInt);
        self::assertEquals('blobTest', $row->testBlob);
        self::assertEquals(1, $row->boolTest);
        self::assertEquals('b', $row->testChar);
        self::assertEquals('2017-11-26', $row->testDate);
        self::assertEquals(1.2, $row->testFloat);
        self::assertEquals('longTextTest', $row->testLongText);
        self::assertEquals(42, $row->mediumIntegerTest);
        self::assertEquals('textMediumTest', $row->mediumText);
        self::assertEquals(2, $row->smallInt);
        self::assertEquals(1, $row->tinyInt);
        self::assertEquals('stringTest', $row->testString);
        self::assertEquals('2017-11-26 15:08:01', $row->testTimestamp);

        Architect::schemaBuilder()->table('mySqlTest')->drop();
    }
}
