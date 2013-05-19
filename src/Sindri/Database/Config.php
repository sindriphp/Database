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

/**
 * @codeCoverageIgnore
 */
class Config {

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
    private $pdoOptions = array();
    /**
     * @var string
     */
    private $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * @param string $dateTimeFormat
     */
    public function setDateTimeFormat($dateTimeFormat) {
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * @return string
     */
    public function getDateTimeFormat() {
        return $this->dateTimeFormat;
    }

    /**
     * @param string $dsn
     */
    public function setDsn($dsn) {
        $this->dsn = $dsn;
    }

    /**
     * @return string
     */
    public function getDsn() {
        return $this->dsn;
    }

    /**
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param string $option
     * @param mixed $value
     */
    public function addPdoOption($option, $value) {
        $this->pdoOptions[] = array($option, $value);
    }

    /**
     * @return array
     */
    public function getPdoOptions() {
        $pdoOptions = array_merge($this->pdoOptions);
        return $pdoOptions;
    }

    /**
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

}
