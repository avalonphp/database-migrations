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

/**
 * Database schema migrator.
 *
 * @package Radium\Database
 * @since 2.0
 * @author Jack Polgar
 */
class Migrator
{
    /**
     * Handles the execution of migrations.
     *
     * @param string $direction
     */
    public static function migrate($direction = 'up')
    {
        if (!Database::connection()->tableExists('schema_migrations')) {
            Schema::create('schema_migrations', function($t){
                $t->varchar('version');
            });
        }

        foreach (get_declared_classes() as $declaredClass) {
            if (is_subclass_of($declaredClass, "Radium\\Database\\Schema\\Migration")) {
                $migration = new $declaredClass;

                if ($direction === 'up') {
                    static::up($declaredClass);
                } else if ($direction === 'down') {
                    static::down($declaredClass);
                }
            }
        }
    }

    /**
     * Migrates the database forward.
     *
     * @param string $className
     */
    protected static function up($className)
    {
        if ($fileName = static::migrated($className)) {
            (new $className)->up();
            Database::connection()->prepare("
                INSERT INTO `schema_migrations` (`version`)
                VALUES (:fileName);
            ")->bindValue('fileName', $fileName)->exec();
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
    protected static function migrated($className)
    {
        $classInfo = new \ReflectionClass($className);
        $fileName  = str_replace('.php', '', basename($classInfo->getFileName()));

        $result = Database::connection()
            ->select()
            ->from('schema_migrations')
            ->where('version = ?', $fileName)
            ->exec();

        if (!$result->rowCount()) {
            return $fileName;
        } else {
            return false;
        }
    }
}
