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
interface Notifier {

    /**
     * @param array $profiler
     */
    public function addProfiler(array $profiler);

    /**
     * @param int $id
     * @param string $queryString
     * @param int $time
     */
    public function notifyProfilingData($id, $queryString, $time);
}