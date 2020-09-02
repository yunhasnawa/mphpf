<?php

namespace m;

use PDO;
use PDOException;

class Model
{
    private $_dbSettings;
    private $_lastExecutedSQL;
    private $_lastWriteException;    // TODO: Consider last read exception and error message
    private $_lastWriteErrorMessage;

    protected $tableName;
    protected $db;

    public function __construct($tableName)
    {
        //pre_print("Constructor called..");

        //pre_print(debug_backtrace());

        $this->_dbSettings = Settings::getInstance()->getDbConnection();

        // TODO: This is get called every time a model is called. Meaning there are more than 1 PDO instances in a single app life time.
        $this->_initDb();

        if($this->db == null)
            die("[M::ERROR] Database connection object is NULL. Please check the connection settings and make sure the database exists.");

        $this->tableName = $tableName;

        $this->_lastWriteException = null;

        $this->_lastWriteErrorMessage = null;
    }

    private function _initDb()
    {
        $connectionString = "mysql:host={$this->_dbSettings['server']};dbname={$this->_dbSettings['database']}";

        try
        {
            $this->db = new PDO(
                $connectionString,
                $this->_dbSettings['username'],
                $this->_dbSettings['password']
            );

            //pre_print($this->db);

            // set the PDO error mode to exception
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return true;
        }
        catch(PDOException $e)
        {
            if($this->_dbSettings['die_on_error'])
                die("[M::ERROR] failed to connect to database server with error message: " . $e->getMessage());
        }
    }

    public function getDb()
    {
        return $this->db;
    }

    public function getLastWriteErrorMessage()
    {
        if($this->_lastWriteException != null)
            return $this->_lastWriteException->getMessage();
        else
        {
            if ($this->_lastWriteErrorMessage != null)
                return $this->_lastWriteErrorMessage;
        }

        return null;
    }

    public function executeWriteSQL($sql, $dieOnError = true)
    {
        $this->_lastWriteException = null;

        try{
            $this->db->exec($sql);

            $this->_lastExecutedSQL = $sql;
        }
        catch (PDOException $e)
        {
            $this->_lastWriteException = $e;

            if($dieOnError)
                die("[M::ERROR] Failed to execute WRITE SQL: <br/>$sql<br/>Error details: <br/>" . $e->getMessage());
            else
                return false;
        }

        return true;
    }

    public function executeReadSQL($sql)
    {
        try {
            $statement = $this->db->prepare($sql);

            $statement->execute();

            $this->_lastExecutedSQL = $sql;

            $statement->setFetchMode(PDO::FETCH_ASSOC);

            $result = array();

            foreach ($statement->fetchAll() as $row) {
                $result[] = $row;
            }

            return $result;
        }
        catch (PDOException $e)
        {
            die("[M::ERROR] Failed to execute READ SQL: <br/>$sql<br/>Error details: <br/>" . $e->getMessage());
        }
    }

    protected function createSqlInsert($columnValuePairs, array $ignoredColumns = array())
    {
        $sql = 'INSERT INTO ' . $this->tableName . ' (';

        $insertedValues = array();

        foreach ($columnValuePairs as $column => $value)
        {
            if(!Util::arrayContains($ignoredColumns, $column))
            {
                $sql .= "$column, ";

                $insertedValues[] = $value;
            }
        }

        $sql = Util::strRemoveLastChars($sql, 2);

        $sql .= ') VALUES (';

        // Ganti tanda petik pada semua value yang di-insertkan dengan karakter petik dari Word..
        foreach ($insertedValues as $value)
        {
            //$value = str_replace("'", "’", $value);

            $value = Util::strReplaceSingleQuoteWithMsWordQuote($value);

            $sql .= "'$value', ";
        }

        $sql = Util::strRemoveLastChars($sql, 2);

        $sql .= ");";

        return $sql;
    }

    protected function createSqlUpdate($columnValuePairs, $whereClause, array $ignoredColumns = array())
    {
        $sql = "UPDATE {$this->tableName} SET ";

        foreach ($columnValuePairs as $column => $value)
        {
            if(!Util::arrayContains($ignoredColumns, $column))
            {
                $value = Util::strReplaceSingleQuoteWithMsWordQuote($value);

                $sql .= "$column = '$value', ";
            }
        }

        $sql = Util::strRemoveLastChars($sql, 2);

        $sql .= " $whereClause";

        return $sql;
    }

    private static function _joinColumnNamesArray($columnNames)
    {
        // Joins the following array structure
        /*
         * Array
           (
               [0] => Array
               (
                   [column_name] => nim_pengusul
               )
               [1] => Array
               (
                   [column_name] => judul_proposal
               )
           )
         */

        $join = array();

        foreach ($columnNames as $row)
            $join[] = $row['column_name'];

        return $join;
    }

    public function getColumnNames()
    {
        // TODO: This only work in MySQL!
        $sql = "SELECT column_name
            FROM information_schema.columns
            WHERE table_name = '{$this->tableName}'
            AND table_schema = '{$this->_dbSettings['database']}';";

        //pre_print($sql);

        $result = $this->executeReadSQL($sql);

        $columnNames = self::_joinColumnNamesArray($result);

        return $columnNames;
    }

    /*
    private static function _stripUnprintableChars($str)
    {
        $str = preg_replace('/[\x00-\x1F\x7F]/u', '', $str);

        return $str;
    }
    */

    public function getAsKeyValuePairs($keyColumn, $valueColumn, $orderByColumn = null, $defaultOptionKey = null, $defaultOptionValue = null, $tableName = null)
    {
        /*
        [6] => Array
        (
            [id] => 7
            [nama] => Dwi Puspitasari, S.Kom., M.Kom.
        )
        */

        $table = $tableName == null ? $this->tableName : $tableName;

        $orderBy = '';

        if($orderByColumn !== null)
            $orderBy = "ORDER BY $orderByColumn";

        $sql = "SELECT $keyColumn, $valueColumn FROM {$table} $orderBy;";

        $result = $this->executeReadSQL($sql);

        if(count($result) < 1)
            return null;

        $pairs = [];

        foreach ($result as $row)
        {
            $pairs[$row[$keyColumn]] = $row[$valueColumn];
        }

        if($defaultOptionKey !== null && $defaultOptionValue !== null)
            $pairs[$defaultOptionKey] = $defaultOptionValue;

        //pre_print($pairs);

        return $pairs;
    }

    public function find(array $lookupColumnValuePairs, array $columnNames = null, $lookupOperator = '=')
    {
        $columns = '*';

        if($columnNames != null)
        {
            $columns = '';

            foreach ($columnNames as $columnName)
            {
                $columns .= "$columnName, ";
            }

            $columns = Util::strRemoveLastChars($columns, 2);
        }

        $sql = "SELECT $columns FROM {$this->tableName} WHERE ";

        $pairsCount = count($lookupColumnValuePairs);

        $lastPair = $pairsCount - 1;

        $counter = 0;

        foreach ($lookupColumnValuePairs as $col => $val)
        {
            $sql .= "$col $lookupOperator '$val'";

            if($counter < $lastPair)
                $sql .= " AND ";
            else
                $sql .= ";";

            $counter++;
        }

        return $this->executeReadSQL($sql);
    }

    public function findOneRowOneColumnValue($columnName, array $lookupColumnValuePairs, $lookupOperator = '=')
    {
        $result = $this->find($lookupColumnValuePairs, [$columnName], $lookupOperator);

        /*
        $sql = "SELECT $columnName FROM {$this->tableName} WHERE ";

        $pairsCount = count($lookupColumnValuePairs);

        $lastPair = $pairsCount - 1;

        $counter = 0;

        foreach ($lookupColumnValuePairs as $col => $val)
        {
            $sql .= "$col $lookupOperator '$val'";

            if($counter < $lastPair)
                $sql .= " AND ";
            else
                $sql .= ";";

            $counter++;
        }

        $result = $this->executeReadSQL($sql);
        */

        if(count($result) > 0)
            return $result[0][$columnName];

        return null;
    }

    protected function setLastWriteErrorMessage($message)
    {
        $this->_lastWriteErrorMessage = $message;
    }

    public static function toNullStringIfEmpty($value)
    {
        if($value == '' || $value == null)
            return 'null';

        return $value;
    }

    protected function createWhereClause(array $keyValuePairs = null)
    {
        $where = '';

        if($keyValuePairs != null)
        {
            $where .= "WHERE ";

            $count = 0;

            foreach ($keyValuePairs as $column => $value)
            {
                $where .= "$column = '$value' ";

                if($count < count($keyValuePairs) - 1)
                    $where .= "AND ";

                $count++;
            }
        }

        return $where;
    }
}