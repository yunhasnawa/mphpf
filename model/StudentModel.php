<?php

namespace model;

use m\Model;

class StudentModel extends Model
{
    public function __construct()
    {
        parent::__construct('student');
    }

    public function findAll()
    {
        $sql = "SELECT * FROM student";

        $records = $this->executeReadSQL($sql);

        return $records;
    }

    public function addNew($name, $address, $phoneNumber)
    {
        $sql = "INSERT INTO {$this->tableName} (name, address, phone_number) VALUES ('$name', '$address', '$phoneNumber')";

        $this->executeWriteSQL($sql);
    }
}
