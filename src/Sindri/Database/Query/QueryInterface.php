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

use \DateTime;
use \Sindri\Database\Exception\InvalidArgumentException;
use \Sindri\Database\Exception\QueryException;
use \Sindri\Database\Exception\DatabaseException;

/**
 * @codeCoverageIgnore
 */
interface QueryInterface {

    /**
     * @param string $format
     *
     * @return QueryInterface
     */
    public function setDateTimeFormat($format);

    /**
     * @param string $key
     * @param int|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindInt($key, $value);

    /**
     * @param string $key
     * @param float|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindFloat($key, $value);

    /**
     * @param string $key
     * @param string|null $value
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindString($key, $value);

    /**
     * @param string $key
     * @param DateTime|null $date
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindDate($key, DateTime $date = null);

    /**
     * This will not change the Timezone of your date object (it clones)
     *
     * @param string $key
     * @param DateTime|null $date
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindDateUTC($key, DateTime $date = null);

    /**
     * @param string $key
     * @param array $values
     *
     * @throws InvalidArgumentException
     * @return QueryInterface
     */
    public function bindArray($key, array $values);

    /**
     * @throws DatabaseException
     * @return QueryInterface
     */
    public function execute();

    /**
     * @param array $prepareBindings
     *
     * @throws QueryException
     * @return QueryInterface
     */
    public function prepare(array $prepareBindings = array());

    /**
     * @throws DatabaseException
     * @return array
     */
    public function fetchAll();

    /**
     * @param mixed $nullValue
     *
     * @throws DatabaseException
     * @return string
     */
    public function fetchValue($nullValue = '');

    /**
     * @param int $column
     *
     * @throws DatabaseException
     * @return array
     */
    public function fetchColumn($column = 0);

    /**
     * @throws DatabaseException
     * @return array
     */
    public function fetchRow();

}
