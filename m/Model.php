<?php

namespace m;

use PDO;
use PDOException;

class Model
{
    private $_dbSettings;
    private $_lastExecutedSQL;

    protected $tableName;
    protected $db;

    public function __construct($tableName)
    {
        $this->_dbSettings = Settings::getInstance()->getDbConnection();

        $this->_initDb();

        $this->tableName = $tableName;
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

    public function executeWriteSQL($sql)
    {
        try{
            $this->db->exec($sql);

            $this->_lastExecutedSQL = $sql;
        }
        catch (PDOException $e)
        {
            die("[M::ERROR] Failed to execute WRITE SQL: <br/>$sql<br/>Error details: <br/>" . $e->getMessage());
        }
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
}