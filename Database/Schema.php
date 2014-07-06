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

namespace Radium\Database;

use Radium\Database;
use Radium\Database\Schema\Table;

/**
 * Database Schema manipulator.
 *
 * @package Radium\Database
 * @since 2.0
 * @author Jack Polgar
 */
class Schema
{
    /**
     * Create table.
     *
     * @param string $table Table name
     * @param lambda $block Table configuration block
     *
     * @return
     */
    public static function create($table, $block)
    {
        $connection = Database::connection();
        $sql = (new Table($table, $block))->create($connection->grammar());
        return $connection->query($sql);
    }

    public static function drop($table)
    {
        $connection = Database::connection();
        return $connection->query($connection->grammar()->dropTable($table));
    }
}
