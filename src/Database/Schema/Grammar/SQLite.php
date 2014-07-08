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
 * SQLite grammar.
 *
 * @package Radium\Database\Schema\Grammar
 * @since 2.0
 * @author Jack Polgar
 */
class SQLite extends Grammar
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

        $sql = array("CREATE TABLE \"{$table->name}\"");
        $sql[] = '(' . PHP_EOL . implode(',' . PHP_EOL, $columns) . PHP_EOL . ')';

        return implode(' ', $sql);
    }

    /**
     * @see Radium\Database\Schema\Grammar::dropTable()
     */
    public function dropTable($table)
    {
        return "DROP TABLE \"{$table}\"";
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
        $sql = array("\"{$column['name']}\"");

        // Column type
        switch($column['type']) {
            case 'TEXT':
                $sql[] = $this->compileText($column);
                break;

            case 'VARCHAR':
                $sql[] = $this->compileText($column);
                break;

            case 'INT':
            case 'TINYINT':
                $sql[] = $this->compileInt($column);
                break;

            case 'DATETIME':
                $sql[] = $this->compileDateTime($column);
                break;
        }

        if ($defaultValue = $this->defaultValue($column)) {
            $sql[] = $defaultValue;
        }

        if ($column['unique']) {
            $sql[] = 'UNIQUE';
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
        $sql = array("INTEGER");

        if ($column['primary']) {
            $sql[] = "PRIMARY KEY";
        }

        if ($column['autoIncrement']) {
            $sql[] = "AUTOINCREMENT";
        }

        if (!$column['nullable']) {
            $sql[] = "NOT NULL";
        }

        return implode(' ', $sql);
    }

    /**
     * Compiles `TEXT` specific SQL.
     *
     * @param array $column Column information
     *
     * @return string
     */
    protected function compileText(array $column)
    {
        $sql = array(strtoupper($column['type']));

        if (!$column['nullable']) {
            $sql[] = "NOT NULL";
        }

        return implode(' ', $sql);
    }

    /**
     * Compiles `DATETIME` specific SQL.
     *
     * @param array $column Column information
     *
     * @return string
     */
    protected function compileDateTime(array $column)
    {
        $sql = array("DATETIME");

        if (!$column['nullable']) {
            $sql[] = "NOT NULL";
        }

        return implode(' ', $sql);
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
        } else if ($column['default'] !== null) {
            return "DEFAULT {$column['default']}";
        }

        return null;
    }
}
