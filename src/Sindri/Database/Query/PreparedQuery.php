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
use \PDOStatement;
use \Sindri\Database\Binding\ValueBinding;
use \Sindri\Database\Exception\DatabaseException;
use \Sindri\Database\Exception\InvalidArgumentException;
use \Sindri\Database\Exception\QueryException;

class PreparedQuery extends BaseQuery {

    /**
     * @param int $id
     * @param PDOStatement $statement
     * @param string $dateTimeFormat
     * @param string[] $keys
     * @param array $arrayBindingsCounter
     */
    public function __construct($id, PDOStatement $statement, $dateTimeFormat, array $keys, array $arrayBindingsCounter) {
        $this->statement = $statement;
        $this->dateTimeFormat = $dateTimeFormat;
        $this->keys = $keys;
        $this->arrayBindingsCounter = $arrayBindingsCounter;
        $this->setId($id);
    }

    /**
     * @throws DatabaseException
     * @return QueryInterface
     */
    public function execute() {
        $this->executeStatment();
        $this->resetBindings();

        return $this;
    }


    /**
     * @param string $key
     * @param int|string|null $value
     * @param int $type
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    protected function bind($key, $value, $type = PDO::PARAM_STR) {
        if (!in_array($key, $this->keys)) {
            throw new InvalidArgumentException("Unknown key: '$key'");
        }
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

        if (!array_key_exists($key, $this->arrayBindingsCounter) || $this->arrayBindingsCounter[$key] != $count) {
            throw new InvalidArgumentException("Wrong number of params to bind array for key: ':$key'");
        }

        $this->bindArrayValues($key, $values);

        return $this;
    }

    /**
     * @param array $prepareBindings
     *
     * @throws QueryException
     * @return QueryInterface
     */
    public function prepare(array $prepareBindings = array()) {
        throw new QueryException("Can´t prepare already prepared Query");
    }

}
