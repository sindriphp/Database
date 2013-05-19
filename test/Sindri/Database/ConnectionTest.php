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
use \Sindri\Database\Connection;
use \PDOStatement;
use \PDO;

/**
 * @covers \Sindri\Database\Connection
 */
class ConnectionTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException \Sindri\Database\Exception\ConnectionException
     * @expectedExceptionMessage Can´t connect to database: 'invalid data source name'
     */
    public function testThrowExceptionWithWrongDsn() {
        $actionQueue = new ActionQueue();
        $connection = new Connection("username", "password", "dsn", array(), $actionQueue);
        $connection->prepare("");
    }

    /**
     * @expectedException \Sindri\Database\Exception\AlreadyConnectedException
     */
    public function testQueueGetsLockedAfterConnected() {
        $actionQueue = new ActionQueue();
        $connection = new Connection("", "", "sqlite::memory:", array(), $actionQueue);
        $connection->run("SELECT 1;");
        $actionQueue->addAction(function () {
            return true;
        });
    }

    public function testCanPreparePDOStatment() {
        $actionQueue = new ActionQueue();
        $connection = new Connection("", "", "sqlite::memory:", array(), $actionQueue);
        $stmt = $connection->prepare("SELECT 1;");
        $this->assertTrue($stmt instanceof PDOStatement);
    }

    public function testIsConnectedPerDefaultFalse() {
        $actionQueue = new ActionQueue();
        $connection = new Connection("", "", "sqlite::memory:", array(), $actionQueue);
        $this->assertFalse($connection->isConnected());
    }

    public function testIsConnectedAfterRun() {
        $actionQueue = new ActionQueue();
        $connection = new Connection("", "", "sqlite::memory:", array(), $actionQueue);
        $this->assertFalse($connection->isConnected());
        $connection->run("SELECT 1;");
        $this->assertTrue($connection->isConnected());
    }

    public function testConnectGetsOnlyCalledOnceWithRun() {
        $actionQueue = new ActionQueue();
        $pdo = new PDO("sqlite::memory:");

        $connectionMock = $this->getMockBuilder("\Sindri\Database\Connection")
            ->setMethods(array('connect'))
            ->setConstructorArgs(array("", "", "sqlite::memory:", array(), $actionQueue))
            ->getMock();

        $connectionMock
            ->expects($this->once())
            ->method('connect')
            ->will($this->returnValue($pdo));

        /** @var $connectionMock Connection */
        $connectionMock->run("SELECT 1;");
        $connectionMock->run("SELECT 1;");
    }

    public function testConnectGetsOnlyCalledOnceWithPrepare() {
        $actionQueue = new ActionQueue();
        $pdo = new PDO("sqlite::memory:");

        $connectionMock = $this->getMockBuilder("\Sindri\Database\Connection")
            ->setMethods(array('connect'))
            ->setConstructorArgs(array("", "", "sqlite::memory:", array(), $actionQueue))
            ->getMock();

        $connectionMock
            ->expects($this->once())
            ->method('connect')
            ->will($this->returnValue($pdo));

        /** @var $connectionMock Connection */
        $connectionMock->prepare("SELECT :ID;");
        $connectionMock->prepare("SELECT :ID;");
    }

    public function testActionQueueGetsCalledAfterConnect() {
        $actionQueue = new ActionQueue();

        $called = 0;
        $that = $this; // Sindri/Database don´t need PHP 5.4 - so we stay 5.3 safe
        $actionQueue->addAction(function ($pdo) use ($that, &$called) {
            $that->assertTrue($pdo instanceof PDO);
            $called++;
        });

        $connection = new Connection("", "", "sqlite::memory:", array(), $actionQueue);
        $connection->run("SELECT 1;");

        $this->assertSame(1, $called);
    }

    /**
     * @expectedException \Sindri\Database\Exception\QueryException
     * @expectedExceptionMessage Can´t run query: 'so;me nonsense"
     */
    public function testThrowsExceptionWithBrokenQueryInRun() {
        $actionQueue = new ActionQueue();
        $connection = new Connection("", "", "sqlite::memory:", array(), $actionQueue);
        $connection->run('so;me nonsense');
    }

    /**
     * @expectedException \Sindri\Database\Exception\QueryException
     * @expectedExceptionMessage Can´t prepare query: 'so;me nonsense"
     */
    public function testThrowsExceptionWithBrokenQueryInPrepare() {
        $actionQueue = new ActionQueue();
        $connection = new Connection("", "", "sqlite::memory:", array(), $actionQueue);
        $stmt = $connection->prepare('so;me nonsense');
        $this->assertNull($stmt);
    }

    public function testCloseConnection() {
        $actionQueue = new ActionQueue();
        $connection = new Connection("", "", "sqlite::memory:", array(), $actionQueue);
        $connection->run("SELECT 1;");
        $this->assertTrue($connection->isConnected());
        $connection->close();
        $this->assertFalse($connection->isConnected());
    }
}
