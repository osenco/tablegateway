<?php

/*
 * The MIT License
 *
 * Copyright 2018 David Bwire <israelbwire@gmail.com>.
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

namespace Bitmarshals\TableGateway;

/**
 * Description of TableGateway
 *
 * @author David Bwire <israelbwire@gmail.com>
 */
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway as ZfTableGateway;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Adapter\Driver\ConnectionInterface;

/**
 * Description of TableGateway
 *
 * @author David Bwire <israelbwire@gmail.com>
 */
class TableGateway extends ZfTableGateway {

    /**
     * Retreive an Sql instance preset with the dbAdapter and tableName
     *
     * @return \Zend\Db\Sql\Sql $sql
     */
    public function getSlaveSql($table = null) {
        if (!empty($table)) {
            return new Sql($this->getAdapter(), $table);
        }
        return new Sql($this->getAdapter(), $this->getTable());
    }

    /**
     * @return \Zend\Db\Sql\Predicate\Predicate Description
     */
    public function getPredicate() {
        return new Predicate();
    }

    /**
     * 
     * @return ConnectionInterface
     */
    public function beginTransaction() {
        return $this->getAdapter()->getDriver()
                        ->getConnection()->beginTransaction();
    }

    /**
     * 
     * @return ConnectionInterface
     */
    public function rollback() {
        return $this->getAdapter()->getDriver()
                        ->getConnection()->rollback();
    }

    /**
     * 
     * @return ConnectionInterface
     */
    public function commit() {
        return $this->getAdapter()->getDriver()
                        ->getConnection()->commit();
    }

    /**
     * @deprecated since version number
     * @param \Exception $ex
     * @return string
     */
    protected function getExceptionSummary(\Exception $ex) {
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
    protected function exceptionSummary(\Exception $ex, $file = null, $line = null) {
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
    protected static function printSqlObject($sqlObject, $sql, $exit = 1) {
        echo '<br>';
        echo '<pre>';
        echo $sql->buildSqlString($sqlObject);
        echo '</pre>';
        if ($exit) {
            exit;
        }
    }

}
