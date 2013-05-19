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

namespace Sindri\Database\Query;

use \PHPUnit_Framework_TestCase;
use \Sindri\Database\ActionQueue;
use \Sindri\Database\Connection;
use \Sindri\Database\Query\OpenQuery;
use \PDO;

/**
 * @covers \Sindri\Database\Query\PreparedQuery
 */
class PreparedQueryTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp() {
        $pdo = new PDO('sqlite::memory:', '', '', array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ));
        $pdo->exec(file_get_contents(__DIR__ . '/../fixtures.sql'));

        $actionQueue = new ActionQueue();

        $connectionMock = $this->getMockBuilder('\Sindri\Database\Connection')
            ->setMethods(array('connect'))
            ->setConstructorArgs(array('', '', 'sqlite::memory:', array(), $actionQueue))
            ->getMock();

        $connectionMock
            ->expects($this->any())
            ->method('connect')
            ->will($this->returnValue($pdo));

        $this->connection = $connectionMock;
    }

    /**
     * @param string $queryString
     * @param string $dateString
     *
     * @return OpenQuery
     */
    private function getQuery($queryString, $dateString = 'Y-m-d H:i:s') {
        $openQuery = new OpenQuery(1, $this->connection, $queryString, $dateString);
        return new ProxyQuery($openQuery);
    }

    public function testCanReusePreparedStatment() {
        $userQuery = $this->getQuery('SELECT * FROM users WHERE id = :ID');

        $user1 = $userQuery->bindString('ID', 1)
            ->fetchRow();

        $user2 = $userQuery->bindString('ID', 2)
            ->fetchRow();

        $this->assertSame('1', $user1['id']);
        $this->assertSame('2', $user2['id']);
    }

    /**
     * @expectedException \Sindri\Database\Exception\DatabaseException
     * @expectedExceptionMessage Unknown key: 'ID2'
     */
    public function testExceptionOnUnknownKeyOnPreparedStatment() {
        $userQuery = $this->getQuery('SELECT * FROM users WHERE id = :ID');
        $userQuery->bindString('ID', 1)
            ->fetchRow();
        $userQuery->bindString('ID2', 2)
            ->fetchRow();
    }

    public function testReuseArrayStatement() {
        $query = $this->getQuery('SELECT * FROM users WHERE id IN (:IDS)');

        $query1 = $query->bindArray('IDS', array(1, 2))
            ->fetchAll();

        $query2 = $query->bindArray('IDS', array(3, 4))
            ->fetchAll();

        $this->assertSame('1', $query1[0]['id']);
        $this->assertSame('2', $query1[1]['id']);
        $this->assertSame('3', $query2[0]['id']);
        $this->assertSame('4', $query2[1]['id']);
    }

    /**
     * @expectedException \Sindri\Database\Exception\InvalidArgumentException
     * @expectedExceptionMessage Wrong number of params to bind array for key: ':IDS'
     */
    public function testReuseArrayStatementThrowsExceptionWithWrongCount() {
        $query = $this->getQuery('SELECT * FROM users WHERE id IN (:IDS)');
        $query->bindArray('IDS', array(1, 2))
            ->fetchAll();
        $query->bindArray('IDS', array(3, 4, 5))
            ->fetchAll();
    }

    /**
     * @expectedException \Sindri\Database\Exception\QueryException
     * @expectedExceptionMessage Can´t prepare already prepared Query
     */
    public function testDoublePreparationThrowsException() {
        $preparedQuery = $this->getQuery('SELECT * FROM users WHERE id = :ID')
            ->prepare(array('ID'));
        $preparedQuery->prepare(array('BLA'));
    }

    /**
     * @expectedException \Sindri\Database\Exception\InvalidArgumentException
     * @expectedExceptionMessage Wrong number of params to bind array for key: ':IDS'
     */
    public function testPreparedWithDifferentSizedArray() {
        $preparedQuery = $this->getQuery('SELECT * FROM users WHERE id IN (:IDS)')
            ->prepare(array('IDS', 2));

        $preparedQuery->bindArray('IDS', array(1, 2))->fetchAll();
        $preparedQuery->bindArray('IDS', array(3, 4, 5))->fetchAll();
    }

}
