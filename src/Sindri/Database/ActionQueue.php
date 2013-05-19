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

use \Iterator;
use \Closure;
use \Sindri\Database\Exception\AlreadyConnectedException;

class ActionQueue implements Iterator {

    /**
     * @var int
     */
    private $pos = 0;
    /**
     * @var Closure[]
     */
    private $actions = array();
    /**
     * @var bool
     */
    private $locked = false;

    /**
     * @param Closure $action
     *
     * @throws AlreadyConnectedException
     */
    public function addAction(Closure $action) {
        if ($this->locked) {
            throw new AlreadyConnectedException("A connection to the database is already established. Can´t add an action now!");
        }
        $this->actions[] = $action;
    }

    /**
     * Can´t add another action now
     */
    public function lock() {
        $this->locked = true;
    }

    /**
     * @codeCoverageIgnore
     * @return Closure
     */
    public function current() {
        return $this->actions[$this->pos];
    }

    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function key() {
        return $this->pos;
    }

    /**
     * @codeCoverageIgnore
     */
    public function next() {
        ++$this->pos;
    }

    /**
     * @codeCoverageIgnore
     */
    public function rewind() {
        $this->pos = 0;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    public function valid() {
        return isset($this->actions[$this->pos]);
    }

    public function reset() {
        $this->rewind();
        $this->actions = array();
    }

}
