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

use \PHPUnit_Framework_TestCase;
use \Sindri\Database\Binding\ValueBinding;
use \PDO;

/**
 * @covers \Sindri\Database\Binding\ValueBinding
 */
class ValueBindingTest extends PHPUnit_Framework_TestCase {

    public function testBinding() {
        $binding = new ValueBinding("key", "value", PDO::PARAM_BOOL);
        $this->assertSame("key", $binding->getKey());
        $this->assertSame("value", $binding->getValue());
        $this->assertSame(PDO::PARAM_BOOL, $binding->getType());
    }
}
