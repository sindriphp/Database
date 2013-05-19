<?php

/**
 * Copyright 2013 Markus Lanz (aka stahlstift)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Sindri\Database;

use \PHPUnit_Framework_TestCase;
use \Sindri\Database\Database;
use \PDO;
use \DateTime;

/**
 * Integration Test
 *
 * @covers \Sindri\Database\Database
 */
class DatabaseTest extends PHPUnit_Framework_TestCase {

    /**
     * @var string
     */
    private static $createSQL = '';

    public static function setUpBeforeClass() {
        self::$createSQL = file_get_contents(__DIR__ . '/fixtures.sql');
    }

    private function getSQLite(Config $config) {
        $config->setUsername('user');
        $config->setDsn('sqlite::memory:');
        $sqliteDb = new Database($config);
        $sqliteDb->run(self::$createSQL);
        return $sqliteDb;
    }

    public function testDatabaseQuery() {
        $sqliteDb = $this->getSQLite(new Config());
        $result = $sqliteDb->query("SELECT * FROM users")
            ->fetchAll();
        $this->assertSame('1', $result[0]['id']);
        $this->assertSame('testuser', $result[0]['username']);
    }

    public function testDatabaseConnected() {
        $sqliteDb = $this->getSQLite(new Config());
        $this->assertTrue($sqliteDb->isConnected());
    }

    public function testDatabaseClose() {
        $sqliteDb = $this->getSQLite(new Config());
        $sqliteDb->close();
        $sqliteDb->run("SELECT 1;");
    }

    public function testExoticSQLLiteActions() {
        $config = new Config();
        $config->setUsername('user');
        $config->setDsn('sqlite::memory:');
        $sqliteDb = new Database($config);

        $sqliteDb->addActionAfterConnect(function (PDO $pdo) {
            $pdo->sqliteCreateFunction('md5rev', function ($string) {
                return strrev(md5($string));
            }, 1);
        });

        $sqliteDb->run(self::$createSQL);

        $row = $sqliteDb->query("SELECT username, md5rev(username) FROM users WHERE id = :ID")
            ->bindInt('ID', 1)
            ->fetchRow();

        $this->assertSame('testuser', $row['username']);
        $this->assertSame('6b39936f45fcf2a20d3de05c6c86c9d5', strrev(md5($row['username'])));
    }

    public function testExoticDateTime() {
        $config = new Config();
        $config->setDateTimeFormat('W-Y');
        $sqliteDb = $this->getSQLite($config);

        $dateTime = new DateTime();
        $assertString = $dateTime->format('W-Y');

        $sqliteDb->query("INSERT INTO datetimespecial VALUES (:DATE)")
            ->bindDate('DATE', $dateTime)
            ->execute();

        $row = $sqliteDb->query("SELECT * FROM datetimespecial")
            ->fetchRow();

        $this->assertSame($assertString, $row['update']);
    }

    public function testDefaultDateTime() {
        $sqliteDb = $this->getSQLite(new Config());

        $dateTime = new DateTime();
        $assertString = $dateTime->format('Y-m-d H:i:s');

        $sqliteDb->query("INSERT INTO datetimestandard VALUES (:DATE)")
            ->bindDate('DATE', $dateTime)
            ->execute();

        $row = $sqliteDb->query("SELECT * FROM datetimestandard")
            ->fetchRow();
        $this->assertSame($assertString, $row['update']);
    }
}
