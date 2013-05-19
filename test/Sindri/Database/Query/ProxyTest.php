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
use \Sindri\Database\Query\QueryInterface;
use \DateTime;
use \PDO;

/**
 * @covers \Sindri\Database\Query\ProxyQuery
 */
class ProxyQueryTest extends PHPUnit_Framework_TestCase {

    private function getQueryMock() {
        $queryMock = $this->getMockBuilder('\Sindri\Database\Query\OpenQuery')
            ->disableOriginalConstructor()
            ->getMock();
        return $queryMock;
    }

    private function prepareProxyQuery($method, $key, $value) {
        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method($method)
            ->with($this->equalTo($key), $this->equalTo($value))
            ->will($this->returnValue($queryMock));

        /** @var $queryMock QueryInterface */
        return new ProxyQuery($queryMock);
    }

    public function bindingsTestDataProvider() {
        return array(
            array('bindInt', 'KEY', 1337),
            array('bindFloat', 'KEY', 11.11),
            array('bindString', 'KEY', "YEAH!"),
            array('bindDate', 'KEY', new DateTime()),
            array('bindDateUTC', 'KEY', new DateTime()),
            array('bindArray', 'KEY', array("super", "mario"))
        );
    }

    /**
     * @dataProvider bindingsTestDataProvider
     */
    public function testBindings($method, $key, $value) {
        $proxyQuery = $this->prepareProxyQuery($method, $key, $value);
        $retVal = call_user_func(array($proxyQuery, $method), $key, $value);

        $this->assertInstanceOf('\Sindri\Database\Query\ProxyQuery', $retVal);
    }

    public function testPrepare() {
        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method('prepare')
            ->with($this->equalTo(array()))
            ->will($this->returnValue($queryMock));

        /** @var $queryMock QueryInterface */
        $proxyQuery = new ProxyQuery($queryMock);
        $query = $proxyQuery->prepare();

        $this->assertInstanceOf('\Sindri\Database\Query\ProxyQuery', $query);
    }

    public function testPrepareWithArray() {
        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method('prepare')
            ->with($this->equalTo(array('ID', array(1337, 'yeah'))))
            ->will($this->returnValue($queryMock));

        /** @var $queryMock QueryInterface */
        $proxyQuery = new ProxyQuery($queryMock);
        $query = $proxyQuery->prepare(array(
            'ID',
            array(1337, 'yeah')
        ));

        $this->assertInstanceOf('\Sindri\Database\Query\ProxyQuery', $query);
    }

    public function testSetCustomDateTime() {
        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method('setDateTimeFormat')
            ->with($this->equalTo('Y-M'))
            ->will($this->returnValue($queryMock));

        /** @var $queryMock QueryInterface */
        $proxyQuery = new ProxyQuery($queryMock);
        $query = $proxyQuery->setDateTimeFormat('Y-M');

        $this->assertInstanceOf('\Sindri\Database\Query\OpenQuery', $query);
    }

    public function testExecute() {
        $preparedQuery = $this->getMockBuilder('\Sindri\Database\Query\PreparedQuery')
            ->disableOriginalConstructor()
            ->getMock();

        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($preparedQuery));

        /** @var $queryMock QueryInterface */
        $proxyQuery = new ProxyQuery($queryMock);
        $query = $proxyQuery->execute();

        $this->assertInstanceOf('\Sindri\Database\Query\PreparedQuery', $query);
    }

    public function testFetchAll() {
        $queryResult = array('super');

        $preparedQuery = $this->getMockBuilder('\Sindri\Database\Query\PreparedQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $preparedQuery->expects($this->once())
            ->method('fetchAll')
            ->with($this->equalTo(PDO::FETCH_ASSOC))
            ->will($this->returnValue($queryResult));

        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($preparedQuery));

        /** @var $queryMock QueryInterface */
        $proxyQuery = new ProxyQuery($queryMock);
        $result = $proxyQuery->fetchAll();

        $this->assertSame($queryResult, $result);
    }

    public function testFetchValue() {
        $preparedQuery = $this->getMockBuilder('\Sindri\Database\Query\PreparedQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $preparedQuery->expects($this->once())
            ->method('fetchValue')
            ->with($this->equalTo(''))
            ->will($this->returnValue('super'));

        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($preparedQuery));

        /** @var $queryMock QueryInterface */
        $proxyQuery = new ProxyQuery($queryMock);
        $queryResult = $proxyQuery->fetchValue();

        $this->assertSame($queryResult, 'super');
    }

    public function testFetchColumn() {
        $preparedQuery = $this->getMockBuilder('\Sindri\Database\Query\PreparedQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $preparedQuery->expects($this->once())
            ->method('fetchColumn')
            ->with($this->equalTo(0))
            ->will($this->returnValue(array()));

        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($preparedQuery));

        /** @var $queryMock QueryInterface */
        $proxyQuery = new ProxyQuery($queryMock);
        $queryResult = $proxyQuery->fetchColumn();

        $this->assertSame($queryResult, array());
    }

    public function testFetchRow() {
        $preparedQuery = $this->getMockBuilder('\Sindri\Database\Query\PreparedQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $preparedQuery->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue(array()));

        $queryMock = $this->getQueryMock();
        $queryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($preparedQuery));

        /** @var $queryMock QueryInterface */
        $proxyQuery = new ProxyQuery($queryMock);
        $queryResult = $proxyQuery->fetchRow();

        $this->assertSame($queryResult, array());
    }
}
