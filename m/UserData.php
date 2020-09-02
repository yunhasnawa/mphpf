<?php

namespace m;

/*
 * This class is intended to manage temporary data on a JSON file unique to each user's session.
 * Cannot be used yet, because write file permission issues.
 */
class UserData
{
    const USERDATA_FOLDER = '/appdata';

    private static $_instance;

    private $_fileName;
    private $_data;
    private $_currentSessionId;

    private function __construct()
    {
        $this->_currentSessionId = Session::getInstance()->currentId();

        if($this->_currentSessionId === null)
            die("[M::ERROR] Cannot create UserData file while current session id is null.");

        $this->_fileName = m_dir() . self::USERDATA_FOLDER . '/' . self::_fileName();

        $this->_initFile();

        $fileContent = file_get_contents($this->_fileName);

        if(empty($fileContent))
            $this->_data = array();
        else
            $this->_data = json_decode($fileContent, true);
    }

    public static function getInstance()
    {
        if(self::$_instance == null)
            self::$_instance = new UserData();

        return self::$_instance;
    }

    private function _commit()
    {
        $fileContent = json_encode($this->_data);

        file_put_contents($this->_fileName, $fileContent);

        pre_print($fileContent, true); // Test
    }

    public function read($key = null)
    {
        if($key == null)
            return $this->_data;

        if(isset($this->_data[$key]))
            return $this->_data[$key];

        return null;
    }

    public function write($key, $data)
    {
        $this->_data[$key] = $data;

        $this->_commit();
    }

    public function remove($key)
    {
        if(isset($this->_data[$key]))
        {
            unset($this->_data[$key]);

            $this->_commit();
        }
    }

    private function _initFile()
    {
        if(!file_exists($this->_fileName))
            file_put_contents($this->_fileName, '{}');
    }

    private function _fileName()
    {
        return "userdata_{$this->_currentSessionId}.json";
    }
}