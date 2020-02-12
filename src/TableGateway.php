<?php

/*
 * The MIT License
 *
 * Copyright 2020 Osen Concepts <hi@osen.co.ke>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Osen\TableGateway;

/**
 * Description of TableGateway
 *
 * @author Osen Concepts <hi@osen.co.ke>
 */

use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGateway as ZfTableGateway;
use Laminas\Db\Sql\Predicate\Predicate;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Sql\SqlInterface;

/**
 * Description of TableGateway
 *
 * @author Osen Concepts <hi@osen.co.ke>
 */
class TableGateway extends ZfTableGateway
{

    /**
     * One dimensional array listing valid column names
     * 
     * @var arrays 
     */
    protected $validColumns = [];

    /**
     * One dimensional array containing Key > Value pairs on how mapping should occur
     * 
     * @var array 
     */
    protected $keyMap = [];

    /**
     * Retreive an Sql instance preset with the dbAdapter and tableName
     *
     * @return \Laminas\Db\Sql\Sql $sql
     */
    public function getSlaveSql($table = null)
    {
        if (!empty($table)) {
            return new Sql($this->getAdapter(), $table);
        }
        return new Sql($this->getAdapter(), $this->getTable());
    }

    /**
     * @return \Laminas\Db\Sql\Predicate\Predicate Description
     */
    public function getPredicate()
    {
        return new Predicate();
    }

    /**
     * 
     * @return ConnectionInterface
     */
    public function beginTransaction()
    {
        return $this->getAdapter()->getDriver()
            ->getConnection()->beginTransaction();
    }

    /**
     * 
     * @return ConnectionInterface
     */
    public function rollback()
    {
        return $this->getAdapter()->getDriver()
            ->getConnection()->rollback();
    }

    /**
     * 
     * @return ConnectionInterface
     */
    public function commit()
    {
        return $this->getAdapter()->getDriver()
            ->getConnection()->commit();
    }

    /**
     * @deprecated since version number
     * @param \Exception $ex
     * @return string
     */
    protected function getExceptionSummary(\Exception $ex)
    {
        return PHP_EOL .
            '>>>Exception' . ' - ' . $ex->getMessage() .
            PHP_EOL . '>>>Exception Code ' . $ex->getCode() .
            PHP_EOL . '>>>File ' . $ex->getFile() . ' Line ' . $ex->getLine();
    }

    /**
     *
     * @param \Exception $ex
     * @param string $file file the error occured in
     * @param string $line line in file where the error occured
     * @return string
     */
    protected function exceptionSummary(\Exception $ex, $file = null, $line = null)
    {
        return PHP_EOL .
            '>>>Exception' . ' - ' . $ex->getMessage() .
            PHP_EOL . '>>>Exception Code ' . $ex->getCode() .
            PHP_EOL . '>>>File ' . $ex->getFile() . ' Line ' . $ex->getLine() .
            PHP_EOL . '>>>Originating File ' . $file .
            PHP_EOL . '>>>Originating Line ' . $line;
    }

    /**
     * Converts an SQL object to it's string representation
     * 
     * @param obj $sqlObject
     * @param Sql $sql
     * @param bool $exit
     */
    protected static function printSqlObject(SqlInterface $sqlObject, $sql, $exit = 1)
    {
        echo '<br>';
        echo '<pre>';
        echo $sql->buildSqlString($sqlObject);
        echo '</pre>';
        if ($exit) {
            exit;
        }
    }

    /**
     * Inserts rows in bulk (within a transaction)
     * 
     * @param array $rows
     * @param array $validColumns
     * @return boolean
     * @throws \Exception
     */
    public function insertBulk(array $rows, array $validColumns = [])
    {

        try {

            $this->beginTransaction();
            $sql = $this->getSlaveSql();

            foreach ($rows as $row) {
                $this->sanitizeColumnNames($row, $validColumns);
                $insert = $sql->insert()
                    ->columns(array_keys($row))
                    ->values(array_values($row));
                $result = $sql->prepareStatementForSqlObject($insert)
                    ->execute();
            }
            $this->commit();
            return true;
        } catch (\Exception $ex) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Takes a data array and changes it's keys to match the keymap
     * Also unset old keys
     * 
     * @param array $data
     * @param array $keyMap Contains hash on how data keys should be mapped
     */
    public function mapKeys(array &$data, array $keyMap = [])
    {
        if (!empty($keyMap)) {
            $this->keyMap = $keyMap;
        }
        $aKeys = array_keys($data);
        foreach ($aKeys as $key) {
            if (array_key_exists($key, $this->keyMap)) {
                // assigned current value to new key
                $data[$this->keyMap[$key]] = $data[$key];
                // unset mapped key
                unset($data[$key]);
            }
        }
    }

    /**
     * 
     * @param array $row
     * @param array $validColumns One dimensional array listing valid column names
     * @return void
     */
    public function sanitizeColumnNames(array &$row, array $validColumns = [])
    {
        if (!empty($validColumns)) {
            $this->validColumns = $validColumns;
        }
        // nothing to do if columns not specified
        if (empty($this->validColumns)) {
            return;
        }
        $keys = array_keys($row);
        foreach ($keys as $key) {
            if (!in_array($key, $this->validColumns, true)) {
                unset($row[$key]);
            }
        }
    }
}
