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
use \PDOException;
use \DateTime;
use \DateTimeZone;
use \Sindri\Database\Exception\DatabaseException;
use \Sindri\Database\Exception\InvalidArgumentException;
use \Sindri\Database\Binding\ValueBinding;
use \Sindri\Database\Connection;

abstract class BaseQuery implements QueryInterface {

    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var string
     */
    protected $queryString = '';
    /**
     * @var string
     */
    protected $dateTimeFormat = '';
    /**
     * @var ValueBinding[]
     */
    protected $bindings = array();
    /**
     * @var int[]
     */
    protected $arrayBindingsCounter = array();
    /**
     * @var string[]
     */
    protected $keys = array();
    /**
     * @var PDOStatement
     */
    protected $statement;

    /**
     * @param string $key
     * @param int $counter
     *
     * @return string
     */
    protected function getTempKeyFormat($key, $counter) {
        return "{$key}__$counter";
    }

    /**
     * @param string $format
     *
     * @return QueryInterface
     */
    public function setDateTimeFormat($format) {
        $this->dateTimeFormat = $format;
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
    abstract protected function bind($key, $value, $type = PDO::PARAM_STR);

    /**
     * @param string $key
     * @param array $values
     */
    protected function bindArrayValues($key, array $values) {
        $i = 0;
        foreach ($values as $value) {
            $bindKey = $this->getTempKeyFormat($key, $i);

            if (is_numeric($value)) {
                if (is_int($value)) {
                    $this->bindInt($bindKey, $value);
                } else {
                    $this->bindFloat($bindKey, $value);
                }
            } elseif ($value instanceof DateTime) {
                $this->bindDate($bindKey, $value);
            } else {
                $this->bindString($bindKey, $value);
            }

            $i++;
        }
    }

    /**
     * @param string $key
     * @param int|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindInt($key, $value) {
        if ($value !== null && !is_numeric($value)) {
            throw new InvalidArgumentException("Value must be numeric. Given: '$value' (" . gettype($value) . ")");
        }

        return $this->bind($key, intval($value), ($value === null) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    }

    /**
     * @param string $key
     * @param float|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindFloat($key, $value) {
        if ($value !== null && !is_numeric($value)) {
            throw new InvalidArgumentException("Value must be numeric. Given: '$value' (" . gettype($value) . ")");
        }

        return $this->bind($key, floatval($value), ($value === null) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }

    /**
     * @param string $key
     * @param string|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindString($key, $value) {
        return $this->bind($key, $value, ($value === null) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }

    /**
     * @param string $key
     * @param DateTime|null $date
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindDate($key, DateTime $date = null) {
        if ($date === null) {
            $bindValue = null;
        } else {
            $bindValue = $date->format($this->dateTimeFormat);
        }

        return $this->bind($key, $bindValue, ($date === null) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }

    /**
     * @param string $key
     * @param DateTime|null $date
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindDateUTC($key, DateTime $date = null) {
        if ($date != null) {
            $date = clone $date;
            $date->setTimezone(new DateTimeZone('UTC'));
        }

        return $this->bindDate($key, $date);
    }

    /**
     * @param string $key
     * @param array $values
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    abstract public function bindArray($key, array $values);

    /**
     * @param array $bindings
     *
     * @internal param array|string $entry ...
     * @return QueryInterface
     */
    abstract public function prepare(array $bindings = array());

    protected function resetBindings() {
        $this->bindings = array();
    }

    protected function doBindings() {
        try {
            foreach ($this->bindings as $binding) {
                $key = ':' . $binding->getKey();
                $this->statement->bindValue($key, $binding->getValue(), $binding->getType());
            }

            $this->statement->execute();
        } catch (PDOException $ex) {
            throw new DatabaseException("The query failed with message: '" . $ex->getMessage() . "'", null, $ex);
        }
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function fetchAll() {
        $this->execute();

        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param mixed $nullValue
     *
     * @return string
     * @throws DatabaseException
     */
    public function fetchValue($nullValue = '') {
        $this->execute();
        $retVal = $this->statement->fetchColumn();

        return ($retVal === false) ? $nullValue : $retVal;
    }

    /**
     * @param int $column
     *
     * @throws DatabaseException
     * @return array
     */
    public function fetchColumn($column = 0) {
        $this->execute();
        $tmp = $this->statement->fetchAll(PDO::FETCH_NUM);

        $retVal = array();
        foreach ($tmp as $row) {
            $retVal[] = $row[$column];
        }

        return $retVal;
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function fetchRow() {
        $tmp = $this->fetchAll();

        return (count($tmp) > 0) ? $tmp[0] : $tmp;
    }
}
