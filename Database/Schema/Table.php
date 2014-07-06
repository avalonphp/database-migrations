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

use Radium\Database\Schema\Grammar\MySQL;
use Radium\Database\Schema\Grammar\SQLite;

/**
 * Table construction class.
 *
 * @package Radium\Database\Schema
 * @since 2.0
 * @author Jack Polgar
 */
class Table
{
    /**
     * Table name
     *
     * @var string
     */
    public $name;

    /**
     * Table columns
     *
     * @var array
     */
    public $columns = array();

    /**
     * Table engine, defaults to InnoDB for MariaDB/Mysql.
     *
     * @var string
     */
    public $engine = 'InnoDB';

    /**
     * Default charset.
     *
     * @var string
     */
    public $defaultCharset = 'utf8';

    /**
     * Default collation.
     *
     * @var string
     */
    public $collation = 'utf8_general_ci';

    /**
     * Primary key.
     *
     * @var string
     */
    public $primaryKey;

    /**
     * @param string   $name  Table name
     * @param callable $block Table config block
     *
     * @example
     *     $table = new Table('users', function($t)){
     *         $t->varchar('username');
     *         $t->varchar('password');
     *     });
     */
    public function __construct($name, callable $block)
    {
        $this->name = $name;

        // Add ID column
        $this->int('id', array(
            'primary'       => true,
            'autoIncrement' => true,
            'nullable'      => false,
            'unsigned'      => true
        ));

        $block($this);
    }

    /**
     * Add column to table.
     *
     * @param string $name    Column name
     * @param array  $options Column options
     *
     * @example
     *     $table->addColumn('group_id', array('type' => "INT", 'length' => 11, 'default' => 2));
     */
    public function addColumn($name, array $options)
    {
        unset($options['name']);

        $defaults = array(
            'name'      => $name,
            'unique'    => false,
            'collation' => "utf8_general_ci"
        );

        $this->columns[] = $options + $defaults;
    }

    /**
     * Add string column.
     *
     * @param string $name    Columm name
     * @param array  $options VARCHAR options
     */
    public function varchar($name, array $options = array())
    {
        $defaults = array(
            'type'      => "VARCHAR",
            'length'    => 255,
            'default'   => null,
            'nullable'  => true,
        );

        $this->addColumn($name, $options + $defaults);
    }

    /**
     * Add integer column.
     *
     * @param string $name    Columm name
     * @param array  $options INT options
     */
    public function int($name, array $options = array())
    {
        $defaults = array(
            'type'          => "INT",
            'length'        => 11,
            'unsigned'      => false,
            'nullable'      => true,
            'default'       => null,
            'autoIncrement' => false,
            'primary'       => false
        );

        if (isset($options['primary'])) {
            $this->primaryKey = $name;
        }

        $this->addColumn($name, $options + $defaults);
    }

    /**
     * Add text column.
     *
     * @param string $name    Columm name
     * @param array  $options TEXT options
     */
    public function text($name, array $options = array())
    {
        $defaults = array(
            'type'     => "TEXT",
            'default'  => null,
            'nullable' => true
        );

        $this->addColumn($name, $options + $defaults);
    }

    /**
     * DateTime column.
     *
     * @param string $name    Columm name
     * @param array  $options DATETIME options
     */
    public function dateTime($name, array $options = array())
    {
        $defaults = array(
            'type'     => "DATETIME",
            'nullable' => true,
            'default'  => null
        );

        $this->addColumn($name, $options + $defaults);
    }

    /**
     * Adds `created_at` and `updated_at` DateTime columns.
     */
    public function timestamps()
    {
        $this->dateTime('created_at', array('nullable' => false));
        $this->dateTime('updated_at');
    }

    /**
     * Creates the table.
     *
     * @return boolean
     */
    public function create($grammar)
    {
        return $grammar->createTable($this);
    }
}
