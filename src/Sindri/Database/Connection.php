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

use \PDO;
use \PDOStatement;
use \PDOException;
use \Sindri\Database\Exception\ConnectionException;
use \Sindri\Database\Exception\QueryException;

class Connection {

    /**
     * @var string
     */
    private $username = '';
    /**
     * @var string
     */
    private $password = '';
    /**
     * @var string
     */
    private $dsn = '';
    /**
     * @var array
     */
    private $options = array();
    /**
     * @var ActionQueue
     */
    private $actionQueue;
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @param string $username
     * @param string $password
     * @param string $dsn
     * @param array $options
     * @param ActionQueue $actionQueue
     */
    public function __construct($username, $password, $dsn, array $options, ActionQueue $actionQueue) {
        $this->username = $username;
        $this->password = $password;
        $this->dsn = $dsn;
        $this->options = array_replace($options, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->actionQueue = $actionQueue;
    }

    /**
     * @param string $queryString
     *
     * @throws QueryException
     * @return PDOStatement|null
     */
    public function prepare($queryString) {
        if (!$this->isConnected()) {
            $this->pdo = $this->connect();
        }

        $retVal = null;
        try {
            $retVal = $this->pdo->prepare($queryString);
        } catch (PDOException $ex) {
            throw new QueryException("Can´t prepare query: '" . $queryString . '". Got Error: "' . $ex->getMessage() . "'", null, $ex);
        }

        return $retVal;
    }

    /**
     * @throws ConnectionException
     * @return PDO|null
     */
    protected function connect() {
        $retVal = null;

        try {
            $retVal = new PDO($this->dsn, $this->username, $this->password, $this->options);
        } catch (PDOException $ex) {
            throw new ConnectionException("Can´t connect to database: '" . $ex->getMessage() . "'", null, $ex);
        }

        $this->actionQueue->lock();

        foreach ($this->actionQueue as $action) {
            $action($retVal);
        }

        return $retVal;
    }

    /**
     * @return bool
     */
    public function isConnected() {
        return ($this->pdo !== null);
    }

    /**
     * @param string $queryString
     *
     * @throws QueryException
     */
    public function run($queryString) {
        if (!$this->isConnected()) {
            $this->pdo = $this->connect();
        }

        try {
            $this->pdo->exec($queryString);
        } catch (PDOException $ex) {
            throw new QueryException("Can´t run query: '" . $queryString . '". Got Error: "' . $ex->getMessage() . "'", null, $ex);
        }
    }

    public function close() {
        $this->pdo = null;
        $this->actionQueue->reset();
    }

}
