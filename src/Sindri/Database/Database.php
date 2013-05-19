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

use \Closure;
use \Sindri\Database\Profiler\Profiler;
use \Sindri\Database\Query\QueryInterface;
use \Sindri\Database\Query\OpenQuery;
use \Sindri\Database\Query\ProxyQuery;

class Database {

    /**
     * @var ActionQueue
     */
    private $actionQueue;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var string
     */
    private $dateTimeFormat = 'Y-m-d H:i:s';
    /**
     * @var int
     */
    private $queryId = 0;
    /**
     * @var Profiler[]
     */
    private $profiler = array();

    /**
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->actionQueue = new ActionQueue();
        $this->dateTimeFormat = $config->getDateTimeFormat();
        $username = $config->getUsername();
        $password = $config->getPassword();
        $dsn = $config->getDsn();
        $options = $config->getPdoOptions();
        $this->connection = new Connection($username, $password, $dsn, $options, $this->actionQueue);
    }

    /**
     * It would be useful if you plan to use the sqlite createAggregate method (for example)
     *
     * @see http://www.php.net/manual/de/pdo.sqlitecreateaggregate.php
     *
     * @param Closure $action
     */
    public function addActionAfterConnect(Closure $action) {
        $this->actionQueue->addAction($action);
    }

    /**
     * @return bool
     */
    public function isConnected() {
        return $this->connection->isConnected();
    }

    /**
     * @param string $queryString
     *
     * @return QueryInterface
     */
    public function query($queryString) {
        $openQuery = new OpenQuery($this->getId(), $this->connection, $queryString, $this->dateTimeFormat);
        $openQuery->addProfiler($this->profiler);

        return new ProxyQuery($openQuery);
    }

    /**
     * @return int
     */
    private function getId() {
        return ++$this->queryId;
    }

    /**
     * @param $queryString
     */
    public function run($queryString) {
        $this->connection->run($queryString);
    }

    public function close() {
        $this->connection->close();
    }

    /**
     * @param Profiler $profiler
     */
    public function addProfiler(Profiler $profiler) {
        $this->profiler[] = $profiler;
    }

}
