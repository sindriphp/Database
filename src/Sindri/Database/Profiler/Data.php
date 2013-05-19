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

namespace Sindri\Database\Profiler;

/**
 * @codeCoverageIgnore
 */
class Data {

    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $queryString;
    /**
     * @var int milliseconds
     */
    private $time;

    /**
     * @param int $id
     * @param string $queryString
     * @param int $time
     */
    public function __construct($id, $queryString, $time) {
        $this->id = $id;
        $this->queryString = $queryString;
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getQueryString() {
        return $this->queryString;
    }

    /**
     * @return int
     */
    public function getTime() {
        return $this->time;
    }

}