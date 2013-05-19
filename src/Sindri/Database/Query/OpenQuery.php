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

use \PDO;
use \Sindri\Database\Binding\ValueBinding;
use \Sindri\Database\Exception\InvalidArgumentException;
use \Sindri\Database\Exception\QueryException;
use \Sindri\Database\Connection;
use \Sindri\Database\Exception\DatabaseException;

class OpenQuery extends BaseQuery {

    /**
     * @param int $id
     * @param Connection $connection
     * @param string $queryString
     * @param string $dateTimeFormat
     */
    public function __construct($id, Connection $connection, $queryString, $dateTimeFormat) {
        $this->connection = $connection;
        $this->queryString = $queryString;
        $this->dateTimeFormat = $dateTimeFormat;
        $this->setId($id);
    }

    /**
     * @param string $key
     * @param int|string|null $value
     * @param int $type
     *
     * @return QueryInterface
     */
    protected function bind($key, $value, $type = PDO::PARAM_STR) {
        $this->keys[] = $key;
        $this->bindings[$key] = new ValueBinding($key, $value, $type);

        return $this;
    }

    /**
     * @param string $key
     * @param array $values
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindArray($key, array $values) {
        $count = count($values);

        $this->queryString = $this->prepareQueryString($key, $count);
        $this->arrayBindingsCounter[$key] = $count;
        $this->bindArrayValues($key, $values);

        return $this;
    }

    /**
     * @param string $key
     * @param int $count
     *
     * @return string
     */
    private function prepareQueryString($key, $count) {
        $replaceString = '';
        for ($i = 0; $i < $count; $i++) {
            $newKey = $this->getTempKeyFormat($key, $i);
            $replaceString .= ":$newKey, ";
        }
        $replaceString = (empty($replaceString)) ? "''" : rtrim($replaceString, ', ');

        return str_replace(":$key", $replaceString, $this->queryString);
    }

    /**
     * @param array $prepareBindings
     *
     * @throws QueryException
     * @return QueryInterface
     */
    public function prepare(array $prepareBindings = array()) {
        if (count($this->bindings) != 0 && count($prepareBindings) != 0) {
            throw new QueryException("You can´t prepare a query which already got bindings with new params");
        }

        foreach ($prepareBindings as $option) {
            if (is_array($option)) {
                $key = $option[0];
                $count = $option[1];
                foreach (range(0, $count) as $i) {
                    $this->keys[] = $this->getTempKeyFormat($key, $i);
                }
                $this->queryString = $this->prepareQueryString($key, $count);
                $this->arrayBindingsCounter[$key] = $count;
            } else {
                $this->keys[] = $option;
            }
        }
        $this->statement = $this->connection->prepare($this->queryString);

        $preparedQuery = new PreparedQuery($this->getId(), $this->statement, $this->dateTimeFormat, $this->keys, $this->arrayBindingsCounter);
        $preparedQuery->addProfiler($this->profiler);
        return $preparedQuery;
    }


    /**
     * @throws DatabaseException
     * @return QueryInterface
     */
    public function execute() {
        $this->statement = $this->connection->prepare($this->queryString);
        $this->executeStatment();
        $this->resetBindings();

        $preparedQuery = new PreparedQuery($this->getId(), $this->statement, $this->dateTimeFormat, $this->keys, $this->arrayBindingsCounter);
        $preparedQuery->addProfiler($this->profiler);
        return $preparedQuery;
    }

}
