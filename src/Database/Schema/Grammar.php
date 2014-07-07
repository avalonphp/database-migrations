<?php
/*!
 * Radium
 * Copyright 2011-2014 Jack Polgar
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

namespace Radium\Database\Schema;

use Radium\Database\Schema\Table;

/**
 * Base grammar class.
 *
 * @package Radium\Database\Schema
 * @since 2.0
 * @author Jack Polgar
 */
abstract class Grammar {
    /**
     * Compiles the table into SQL.
     *
     * @param Radium\Database\Schemea\Table $table
     *
     * @return string
     */
    abstract public function createTable(Table $table);

    /**
     * Drop table SQL.
     *
     * @param string $table Table name
     */
    abstract public function dropTable($table);
}
