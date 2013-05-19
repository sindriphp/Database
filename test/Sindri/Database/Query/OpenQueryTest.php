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
use \DateTime;

/**
 * @covers \Sindri\Database\Query\OpenQuery
 */
class OpenQueryTest extends PHPUnit_Framework_TestCase {

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
        $openQuery = new OpenQuery($this->connection, $queryString, $dateString);
        return new ProxyQuery($openQuery);
    }

    public function testPreparedWithArray() {
        $preparedQuery = $this->getQuery('SELECT * FROM users WHERE id IN (:IDS)')
            ->prepare(array(array('IDS', 2)));

        $result1 = $preparedQuery->bindArray('IDS', array(1, 2))->fetchAll();
        $result2 = $preparedQuery->bindArray('IDS', array(3, 4))->fetchAll();

        $this->assertSame('1', $result1[0]['id']);
        $this->assertSame('2', $result1[1]['id']);
        $this->assertSame('3', $result2[0]['id']);
        $this->assertSame('4', $result2[1]['id']);
    }

    public function testPrepared() {
        $preparedQuery = $this->getQuery('SELECT * FROM users WHERE id = :ID')
            ->prepare(array('ID'));

        $result1 = $preparedQuery->bindInt('ID', 1)->fetchRow();
        $result2 = $preparedQuery->bindInt('ID', 2)->fetchRow();

        $this->assertSame('1', $result1['id']);
        $this->assertSame('2', $result2['id']);
    }

    /**
     * @expectedException \Sindri\Database\Exception\QueryException
     * @expectedExceptionMessage You can´t prepare a query which already got bindings with new params
     */
    public function testCantPreparedQueryWhichAlreadyGotBindings() {
        $query = $this->getQuery('SELECT * FROM users WHERE id = :ID')
            ->bindInt('ID', 1);

        // lots of code...

        $query->prepare(array('ID'));
    }

    public function testBindIntArray() {
        $users = $this->getQuery('SELECT * FROM users WHERE id IN (:IDS)')
            ->bindArray('IDS', array(1, 2))
            ->fetchAll();

        $this->assertSame('1', $users[0]['id']);
        $this->assertSame('2', $users[1]['id']);
    }

    public function testBindStringArray() {
        $users = $this->getQuery('SELECT * FROM users WHERE username IN (:NAMES)')
            ->bindArray('NAMES', array('testuser', 'testuser2'))
            ->fetchAll();

        $this->assertSame('1', $users[0]['id']);
        $this->assertSame('2', $users[1]['id']);
    }

    public function testBindMixedArray() {
        $users = $this->getQuery('SELECT * FROM users WHERE id IN (:IDS)')
            ->bindArray('IDS', array('1', 2, 3.00))
            ->fetchAll();

        $this->assertSame('1', $users[0]['id']);
        $this->assertSame('2', $users[1]['id']);
        $this->assertSame('3', $users[2]['id']);
    }

    public function testMultipleBindArray() {
        $users = $this->getQuery('SELECT * FROM users WHERE id IN (:IDS) OR username IN (:NAMES)')
            ->bindArray('IDS', array(1, 2))
            ->bindArray('NAMES', array('testuser3', 'testuser4'))
            ->fetchAll();

        $this->assertSame('1', $users[0]['id']);
        $this->assertSame('2', $users[1]['id']);
        $this->assertSame('3', $users[2]['id']);
        $this->assertSame('4', $users[3]['id']);
    }

    public function testMultipleArrayBindsAndValues() {
        $users = $this->getQuery(
            'SELECT
                *
            FROM
                users
            WHERE
                id IN (:IDS) OR
                username IN (:NAMES) OR
                id = :ID OR
                username = :USERNAME
            ')
            ->bindArray('IDS', array(1, 2))
            ->bindArray('NAMES', array('testuser3', 'testuser4'))
            ->bindInt('ID', 5)
            ->bindString('USERNAME', 'testuser5')
            ->fetchAll();

        $this->assertSame('1', $users[0]['id']);
        $this->assertSame('2', $users[1]['id']);
        $this->assertSame('3', $users[2]['id']);
        $this->assertSame('4', $users[3]['id']);
        $this->assertSame('5', $users[4]['id']);
    }

    public function testDateTimeArray() {
        $birth1 = new DateTime('1983-12-10T04:03:01+0100');
        $birth2 = new DateTime('1950-03-03T14:03:01+0100');

        $this->getQuery('INSERT INTO `datetimespecial` VALUES (:MYDATE)')
            ->bindDate('MYDATE', $birth1)
            ->execute();

        $this->getQuery('INSERT INTO `datetimespecial` VALUES (:MYDATE)')
            ->bindDate('MYDATE', $birth2)
            ->execute();

        $dates = array($birth1, $birth2);
        $result = $this->getQuery('SELECT * FROM `datetimespecial` WHERE `update` IN (:DATES)')
            ->bindArray('DATES', $dates)
            ->fetchAll();

        $this->assertSame(array(
            array(
                'update' => "1983-12-10 04:03:01"
            ),
            array(
                'update' => "1950-03-03 14:03:01"
            )
        ), $result);
    }

}
