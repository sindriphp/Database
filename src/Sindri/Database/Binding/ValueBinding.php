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

namespace Sindri\Database\Binding;

use \PDO;
use \DateTime;

class ValueBinding {

    /**
     * @var string
     */
    private $key = '';
    /**
     * @var int|string|DateTime|null
     */
    private $value;
    /**
     * @var int
     */
    private $type = PDO::PARAM_STR;

    /**
     * @param string $key
     * @param int $type
     * @param int|string|DateTime|null $value
     */
    public function __construct($key, $value, $type) {
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return DateTime|int|null|string
     */
    public function getValue() {
        return $this->value;
    }

}
