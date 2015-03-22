<?php
/*!
 * Avalon
 * Copyright 2011-2015 Jack Polgar
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

namespace Avalon\Database;

use Avalon\Database\ConnectionManager;
use Doctrine\DBAL\Schema\Schema;

/**
 * Base migration class.
 *
 * @package Avalon\Database
 * @author Jack Polgar <jack@polgar.id.au>
 */
abstract class Migration
{
    /**
     * @var object
     */
    protected $connection;

    /**
     * @var object
     */
    protected $schemaManager;

    /**
     * @var Schema
     */
    protected $currentSchema;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @param Schema $schema
     */
    public function __construct()
    {
        $this->connection    = ConnectionManager::getConnection();
        $this->schemaManager = $this->connection->getSchemaManager();

        $this->currentSchema = $this->schemaManager->createSchema();
        $this->schema        = clone $this->currentSchema;
    }

    /**
     * Forward migration.
     */
    abstract public function up();

    /**
     * Rollback migration.
     */
    abstract public function down();

    /**
     * Create table.
     */
    final protected function createTable($name, callable $func)
    {
        $table = $this->schema->createTable($name);
        $table->addColumn("id", "bigint");

        $func($table);

        return $table;
    }

    final protected function modifyTable($name, callable $func)
    {
        $table = $this->schema->getTable($name);

        $func($table);

        return $table;
    }

    /**
     * Delete table.
     */
    final protected function dropTable($name)
    {
        return $this->schema->dropTable($name);
    }

    /**
     * Add timestamps columns to the table.
     *
     * @param object $table
     */
    final protected function timestamps($table)
    {
        $table->addColumn("created_at", "datetime");
        $table->addColumn("updated_at", "datetime");
    }

    /**
     * Execute the migrations queries.
     */
    final function execute()
    {
        $platform = $this->connection->getDatabasePlatform();
        $queries  = $this->currentSchema->getMigrateToSql($this->schema, $platform);

        // echo "<pre>";
        // print_r($queries);
        // exit;

        foreach ($queries as $query) {
            $this->connection->query($query);
        }
    }
}
