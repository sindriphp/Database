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
 * @covers \Sindri\Database\Query\BaseQuery
 */
class BaseQueryTest extends PHPUnit_Framework_TestCase {

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

    public function testLikeQuery() {
        $users = $this->getQuery('SELECT * FROM users WHERE username LIKE :USERLIKE')
            ->bindString('USERLIKE', '%estu%')
            ->fetchAll();

        $this->assertSame(5, count($users));
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

    /**
     * @expectedException \Sindri\Database\Exception\QueryException
     * @expectedExceptionMessage  Can´t prepare query: 'SELECT * FROM typo WHERE ID = :ID". Got Error: "SQLSTATE[HY000]: General error: 1 no such table: typo'
     */
    public function testBindOnNonExistingTable() {
        $this->getQuery('SELECT * FROM typo WHERE ID = :ID')
            ->bindInt('ID', 1)
            ->fetchAll();
    }

    public function testBindInt() {
        $user = $this->getQuery('SELECT * FROM users WHERE id = :ID')
            ->bindInt('ID', 1)
            ->fetchRow();
        $this->assertSame('1', $user['id']);
    }

    /**
     * @expectedException \Sindri\Database\Exception\InvalidArgumentException
     * @expectedExceptionMessage Value must be numeric. Given: 'A' (string)
     */
    public function testBindIntThrowsExceptionWithAlphaLetter() {
        $query = $this->getQuery('SELECT * FROM users WHERE id = :ID');
        $query->bindInt('ID', 'A');
    }

    public function testBindIntWithNumericString() {
        $user = $this->getQuery('SELECT * FROM users WHERE id = :ID')
            ->bindInt('ID', '1')
            ->fetchRow();
        $this->assertSame('1', $user['id']);
    }

    public function testBindIntWithFloat() {
        $user = $this->getQuery('SELECT * FROM users WHERE id = :ID')
            ->bindInt('ID', 1.00)
            ->fetchRow();
        $this->assertSame('1', $user['id']);
    }

    public function testBindIntWithWeiredFloat() {
        $user = $this->getQuery('SELECT * FROM users WHERE id = :ID')
            ->bindInt('ID', 1.337)
            ->fetchRow();
        $this->assertSame('1', $user['id']);
    }

    public function testBindIntWithNull() {
        $user = $this->getQuery('SELECT * FROM users WHERE username = :NAME')
            ->bindInt('NAME', null)
            ->fetchRow();
        $this->assertSame(0, count($user));
    }

    public function testBindFloat() {
        $data = $this->getQuery('SELECT * FROM numericdata WHERE datafield = :DATA')
            ->bindFloat('DATA', 13.37)
            ->fetchRow();
        $this->assertSame('3', $data['id']);
    }

    /**
     * @expectedException \Sindri\Database\Exception\InvalidArgumentException
     * @expectedExceptionMessage Value must be numeric. Given: 'A' (string)
     */
    public function testBindFloatThrowsExceptionWithAlphaLetter() {
        $query = $this->getQuery('SELECT * FROM numericdata WHERE datafield = :DATA');
        $query->bindFloat('DATA', 'A');
    }

    public function testBindFloatWithNumericString() {
        $data = $this->getQuery('SELECT * FROM numericdata WHERE datafield = :DATA')
            ->bindFloat('DATA', '13.37')
            ->fetchRow();
        $this->assertSame('3', $data['id']);
    }

    public function testBindFloatWithInt() {
        $data = $this->getQuery('SELECT * FROM numericdata WHERE datafield = :DATA')
            ->bindFloat('DATA', 1)
            ->fetchRow();
        $this->assertSame('4', $data['id']);
    }

    public function testBindFloatWithNull() {
        $data = $this->getQuery('SELECT * FROM numericdata WHERE datafield = :DATA')
            ->bindFloat('DATA', null)
            ->fetchRow();
        $this->assertSame(0, count($data));
    }

    public function testBindString() {
        $user = $this->getQuery('SELECT * FROM users WHERE id = :ID')
            ->bindString('ID', '1')
            ->fetchRow();
        $this->assertSame('1', $user['id']);
    }

    public function testBindStringWithNull() {
        $user = $this->getQuery('SELECT * FROM users WHERE username = :NAME')
            ->bindString('NAME', null)
            ->fetchRow();
        $this->assertSame(0, count($user));
    }

    public function testBindDateTimeWithModifiedDateString() {
        $this->getQuery('INSERT INTO `datetimespecial` VALUES (:MYDATE)')
            ->setDateTimeFormat('Y-M')
            ->bindDate('MYDATE', new DateTime('1983-12-10T04:03:01+0100'))
            ->execute();

        $result = $this->getQuery('SELECT * FROM `datetimespecial`')
            ->fetchAll();

        $this->assertSame('1983-Dec', $result[0]['update']);
    }

    public function testBindDateTime() {
        $this->getQuery('INSERT INTO `datetimespecial` VALUES (:MYDATE)')
            ->bindDate('MYDATE', new DateTime('1983-12-10T04:03:01+0100'))
            ->execute();

        $result = $this->getQuery('SELECT * FROM `datetimespecial`')
            ->fetchAll();

        $this->assertSame('1983-12-10 04:03:01', $result[0]['update']);
    }

    public function testBindDateTimeUTC() {
        $this->getQuery('INSERT INTO `datetimespecial` VALUES (:MYDATE)')
            ->bindDateUTC('MYDATE', new DateTime('1983-12-10T04:03:01+0100'))
            ->execute();

        $result = $this->getQuery('SELECT * FROM `datetimespecial`')
            ->fetchAll();

        $this->assertSame('1983-12-10 03:03:01', $result[0]['update']);
    }

    public function testBindDateTimeUTCNotModifyOriginalDate() {
        $myDate = new DateTime('1983-12-10T04:03:01+0100');
        $this->getQuery('INSERT INTO `datetimespecial` VALUES (:MYDATE)')
            ->bindDateUTC('MYDATE', $myDate)
            ->execute();

        $this->assertTrue($myDate->getTimezone()->getName() != 'UTC');
    }

    public function testBindDateTimeWithWeiredFormat() {
        $dateTimeFormat = 'D-j_w:W_F-m_M/L|o_aAB:G';
        $this->getQuery('INSERT INTO `datetimespecial` VALUES (:MYDATE)', $dateTimeFormat)
            ->bindDate('MYDATE', new DateTime('1983-12-10T04:03:01+0100'))
            ->execute();

        $result = $this->getQuery('SELECT * FROM `datetimespecial`')
            ->fetchAll();

        $this->assertSame('Sat-10_6:49_December-12_Dec/0|1983_amAM168:4', $result[0]['update']);
    }

    public function testBindDateWithNull() {
        $this->getQuery('INSERT INTO `datetimespecial` VALUES (:MYDATE)')
            ->bindDate('MYDATE', new DateTime('1983-12-10T04:03:01+0100'))
            ->execute();

        $result = $this->getQuery('SELECT * FROM `datetimespecial` WHERE `update` = :MYDATE')
            ->bindDate('MYDATE', null)
            ->fetchAll();

        $this->assertSame(array(), $result);
    }

    public function testMultipleTypesArray() {
        $users = $this->getQuery('
            SELECT * FROM users WHERE id IN (:IDS) OR
            username IN (:NAMES) OR
            username IN (:DATES) OR
            username IN (:MONEY)')
            ->bindArray('IDS', array(1, 2))
            ->bindArray('NAMES', array('testuser3', 'testuser4'))
            ->bindArray('DATES', array(new DateTime(), new DateTime()))
            ->bindArray('MONEY', array(1.337, 42.23))
            ->fetchAll();

        $this->assertSame('1', $users[0]['id']);
        $this->assertSame('2', $users[1]['id']);
        $this->assertSame('3', $users[2]['id']);
        $this->assertSame('4', $users[3]['id']);
    }

    public function testFetchAll() {
        $result = $this->getQuery('SELECT * FROM users')
            ->fetchAll();

        $this->assertSame(array(
            0 =>
            array(
                'id' => '1',
                'username' => 'testuser',
                'password' => 'asdfasdfasdfasdfasdfasdfasdfasdf',
            ),
            1 =>
            array(
                'id' => '2',
                'username' => 'testuser2',
                'password' => 'asdfasdfasdfasdfasdfasdfasdfasdf',
            ),
            2 =>
            array(
                'id' => '3',
                'username' => 'testuser3',
                'password' => 'asdfasdfasdfasdfasdfasdfasdfasdf',
            ),
            3 =>
            array(
                'id' => '4',
                'username' => 'testuser4',
                'password' => 'asdfasdfasdfasdfasdfasdfasdfasdf',
            ),
            4 =>
            array(
                'id' => '5',
                'username' => 'testuser5',
                'password' => 'asdfasdfasdfasdfasdfasdfasdfasdf',
            ),
            5 =>
            array(
                'id' => '6',
                'username' => '0',
                'password' => 'asdfasdfasdfasdfasdfasdfasdfasdf',
            ),
        ), $result);
    }

    public function testFetchValue() {
        $username = $this->getQuery('SELECT username FROM users WHERE id = :ID')
            ->bindInt('ID', 1)
            ->fetchValue();

        $this->assertSame('testuser', $username);
    }

    public function testFetchValueWithCustomNullValue() {
        $username = $this->getQuery('SELECT username FROM users WHERE id = :ID')
            ->bindInt('ID', 100)
            ->fetchValue('customNullValue');

        $this->assertSame('customNullValue', $username);
    }

    public function testFetchValueWithNullValue() {
        $username = $this->getQuery('SELECT username FROM users WHERE id = :ID')
            ->bindInt('ID', 100)
            ->fetchValue();

        $this->assertSame('', $username);
    }

    public function testFetchColumn() {
        $result = $this->getQuery('SELECT * FROM users')
            ->fetchColumn();

        $this->assertSame(array(
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4',
            4 => '5',
            5 => '6'
        ), $result);
    }

    public function testFetchColumnWithCustomColumn() {
        $result = $this->getQuery('SELECT * FROM users')
            ->fetchColumn(1);

        $this->assertSame(array(
            0 => 'testuser',
            1 => 'testuser2',
            2 => 'testuser3',
            3 => 'testuser4',
            4 => 'testuser5',
            5 => '0'
        ), $result);
    }

    public function testRow() {
        $result = $this->getQuery('SELECT * FROM users')
            ->fetchRow();

        $this->assertSame(array(
            'id' => '1',
            'username' => 'testuser',
            'password' => 'asdfasdfasdfasdfasdfasdfasdfasdf',
        ), $result);
    }
}
