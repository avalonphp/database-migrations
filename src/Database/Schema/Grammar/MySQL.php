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

namespace Radium\Database\Schema\Grammar;

use Radium\Database\Schema\Grammar;
use Radium\Database\Schema\Table;

/**
 * MySQL/MariaDB grammar.
 *
 * @package Radium\Database\Schema\Grammar
 * @since 2.0
 * @author Jack Polgar
 */
class MySQL extends Grammar
{
    /**
     * @see Radium\Database\Schema\Grammar::createTable()
     */
    public function createTable(Table $table)
    {
        $columns = array();

        foreach ($table->columns as $column) {
            $columns[] = "  " . $this->compileColumn($column);
        }

        if ($table->primaryKey) {
            $columns[] = "  PRIMARY KEY (`{$table->primaryKey}`)";
        }

        $sql = array("CREATE TABLE `{$table->name}`");
        $sql[] = '(' . PHP_EOL . implode(',' . PHP_EOL, $columns) . PHP_EOL . ')';
        $sql[] = "ENGINE {$table->engine}";
        $sql[] = "DEFAULT CHARSET {$table->defaultCharset}";
        $sql[] = "COLLATE {$table->collation};";

        return implode(' ', $sql);
    }

    /**
     * @see Radium\Database\Schema\Grammar::dropTable()
     */
    public function dropTable($table)
    {
        return "DROP TABLE `{$table}`;";
    }

    /**
     * Compiles the column into SQL.
     *
     * @param array $column Column information
     *
     * @return string
     */
    protected function compileColumn(array $column)
    {
        $sql = array("`{$column['name']}`");

        // Column type
        switch($column['type']) {
            case 'VARCHAR':
                $sql[] = $this->compileVarChar($column);
                break;

            case 'TEXT':
                $sql[] = "TEXT";
                break;

            case 'LONGTEXT':
                $sql[] = 'longtext';
                break;

            case 'INT':
            case 'TINYINT':
                $sql[] = $this->compileInt($column);
                break;

            case 'DATETIME':
                $sql[] = 'datetime';
                break;
        }

        // Collation
        if (isset($column['collation'])) {
            $sql[] = "COLLATE {$column['collation']}";
        }

        // Nullable
        if (!$column['nullable']) {
            $sql[] = 'NOT NULL';
        }

        // Default value
        if ($defaultValue = $this->defaultValue($column)) {
            $sql[] = $defaultValue;
        }

        if (isset($column['primary']) and $column['primary']
        and isset($column['autoIncrement']) and $column['autoIncrement']) {
            $sql[] = 'AUTO_INCREMENT';
        }

        return implode(' ', $sql);
    }

    /**
     * Compiles `INT` specific SQL.
     *
     * @param array $column Column information
     *
     * @return string
     */
    protected function compileInt(array $column)
    {
        $sql = array(strtolower($column['type']) . "({$column['size']})");

        if ($column['unsigned']) {
            $sql[] = 'unsigned';
        }

        return implode(' ', $sql);
    }

    /**
     * Compiles `VARCHAR` specific SQL.
     *
     * @param array $column Column information
     *
     * @return string
     */
    protected function compileVarChar(array $column)
    {
        return "varchar({$column['size']})";
    }

    /**
     * Compiles the default value SQL.
     *
     * @param array $column Column information
     *
     * @return string
     */
    protected function defaultValue(array $column)
    {
        if ($column['nullable'] === false and $column['default'] === null) {
            return;
        } else if ($column['default'] === null) {
            return "DEFAULT NULL";
        } else {
            return "DEFAULT '{$column['default']}'";
        }
    }
}
