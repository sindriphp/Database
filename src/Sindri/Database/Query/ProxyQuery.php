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
use \DateTime;
use \Sindri\Database\Exception\DatabaseException;
use \Sindri\Database\Exception\InvalidArgumentException;
use \Sindri\Database\Query\QueryInterface;

class ProxyQuery implements QueryInterface {

    /**
     * @var QueryInterface
     */
    protected $query;

    /**
     * @param QueryInterface $query
     */
    public function __construct(QueryInterface $query) {
        $this->query = $query;
    }

    /**
     * @param string $format
     *
     * @return QueryInterface
     */
    public function setDateTimeFormat($format) {
        return $this->query->setDateTimeFormat($format);
    }

    /**
     * @param string $key
     * @param int|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindInt($key, $value) {
        $this->query = $this->query->bindInt($key, $value);

        return $this;
    }

    /**
     * @param string $key
     * @param float|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindFloat($key, $value) {
        $this->query = $this->query->bindFloat($key, $value);

        return $this;
    }

    /**
     * @param string $key
     * @param string|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindString($key, $value) {
        $this->query = $this->query->bindString($key, $value);

        return $this;
    }

    /**
     * @param string $key
     * @param DateTime|null $date
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindDate($key, DateTime $date = null) {
        $this->query = $this->query->bindDate($key, $date);

        return $this;
    }

    /**
     * @param string $key
     * @param DateTime|null $date
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindDateUTC($key, DateTime $date = null) {
        $this->query = $this->query->bindDateUTC($key, $date);

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
        $this->query = $this->query->bindArray($key, $values);

        return $this;
    }

    /**
     * @param array $bindings
     *
     * @internal param array|string $entry ...
     * @return QueryInterface
     */
    public function prepare(array $bindings = array()) {
        $this->query = $this->query->prepare($bindings);

        return $this;
    }

    /**
     * @throws DatabaseException
     * @return QueryInterface
     */
    public function execute() {
        $this->query = $this->query->execute();

        return $this->query;
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function fetchAll() {
        $this->execute();

        return $this->query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param mixed $nullValue
     *
     * @return string
     * @throws DatabaseException
     */
    public function fetchValue($nullValue = '') {
        $this->execute();

        return $this->query->fetchValue($nullValue);
    }

    /**
     * @param int $column
     *
     * @throws DatabaseException
     * @return array
     */
    public function fetchColumn($column = 0) {
        $this->execute();

        return $this->query->fetchColumn($column);
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function fetchRow() {
        $this->execute();

        return $this->query->fetchRow();
    }

}
