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

use DateTime;
use ReflectionClass;
use Avalon\Database\ConnectionManager;
use Doctrine\DBAL\Schema\Schema;

/**
 * Database migrator.
 *
 * @package Avalon\Database
 * @author Jack Polgar <jack@polgar.id.au>
 */
class Migrator
{
    /**
     * @var object
     */
    protected $connection;

    public function __construct()
    {
        $this->connection = ConnectionManager::getConnection();
    }

    /**
     * Handles the execution of migrations.
     *
     * @param string $direction
     */
    public function migrate($direction = 'up')
    {
        if (!$this->tableExists('schema_migrations')) {
            $this->createMigrationsTable();
        }

        foreach (get_declared_classes() as $declaredClass) {
            if (is_subclass_of($declaredClass, "Avalon\\Database\\Migration")) {
                if ($direction === 'up') {
                    $this->up($declaredClass);
                } elseif ($direction === 'down') {
                    $this->down($declaredClass);
                }
            }
        }
    }

    /**
     * Migrates the database forward.
     *
     * @param string $className
     */
    protected function up($className)
    {
        if ($fileName = $this->migrated($className)) {
            $migration = new $className;
            $migration->up();
            $migration->execute();

            $this->connection->insert("schema_migrations", [
                'version' => $fileName,
                'date'    => date("Y-m-d")
            ]);
        }
    }

    /**
     * Checks if the migration has been executed and returns the migrations
     * base file name.
     *
     * @param string $className
     *
     * @return string
     */
    protected function migrated($className)
    {
        $classInfo = new ReflectionClass($className);
        $fileName  = str_replace('.php', '', basename($classInfo->getFileName()));

        $result = $this->connection->createQueryBuilder()->select('*')
            ->from('schema_migrations')
            ->where('version = ?')
            ->setParameter(0, $fileName)
            ->execute();

        if (!$result->rowCount()) {
            return $fileName;
        } else {
            return false;
        }
    }

    /**
     * Check if the table exists.
     *
     * @param string $tableName
     *
     * @return boolean
     */
    protected function tableExists($tableName)
    {
        $tables = $this->connection->getSchemaManager()->listTables();

        foreach ($tables as $table) {
            if ($table->getName() == $tableName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates the migration log table.
     */
    protected function createMigrationsTable()
    {
        $schema = new Schema;
        $table = $schema->createTable("schema_migrations");
        $table->addColumn("version", "string");
        $table->addColumn("date", "date");

        $queries = $schema->toSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            $this->connection->query($query);
        }
    }
}
