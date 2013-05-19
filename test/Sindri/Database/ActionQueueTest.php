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
use \Sindri\Database\ActionQueue;

/**
 * @covers \Sindri\Database\ActionQueue
 */
class ActionQueueTest extends PHPUnit_Framework_TestCase {

    public function testQueue() {
        $queue = new ActionQueue();
        $counter = 0;

        $queue->addAction(function () use (&$counter) {
            $counter++;
            return true;
        });

        $queue->addAction(function () use (&$counter) {
            $counter++;
            return true;
        });

        foreach ($queue as $action) {
            $this->assertTrue($action());
        }

        $this->assertSame(2, $counter);

    }

    /**
     * @expectedException \Sindri\Database\Exception\AlreadyConnectedException
     */
    public function testThrowsExceptionIfQueueIsLocked() {
        $queue = new ActionQueue();
        $queue->lock();
        $queue->addAction(function () {
            return true;
        });
    }

    public function testReset() {
        $queue = new ActionQueue();
        $counter = 0;

        $queue->addAction(function () use (&$counter) {
            $counter++;
            return true;
        });

        $queue->reset();

        foreach ($queue as $action) {
            $this->assertTrue($action());
        }

        $this->assertSame(0, $counter);
    }
}
