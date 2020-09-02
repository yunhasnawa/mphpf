<?php

namespace m;

class Session
{
    const M_SESSION_KEY = 'M_SESSION';

    private static $_instance;

    private $_data;

    private function __construct()
    {
        $this->_init();
    }

    public static function getInstance()
    {
        if(self::$_instance == null)
            self::$_instance = new Session();

        return self::$_instance;
    }

    private function _sessionStarted()
    {
        $phpBelow5_4 = version_compare(PHP_VERSION, '5.4.0') >= 0;

        if($phpBelow5_4)
            return session_id() != '';
        else
            return session_status() != PHP_SESSION_NONE;
    }

    private function _mSessionExists()
    {
        return isset($_SESSION[self::M_SESSION_KEY]);
    }
    
    private function _init()
    {
        if(!$this->_sessionStarted())
            session_start();

        if(!$this->_mSessionExists())
            $_SESSION[self::M_SESSION_KEY] = array();

        $this->_data = &$_SESSION[self::M_SESSION_KEY];
    }

    public function end()
    {
        if($this->_sessionStarted() == true)
        {
            unset($_SESSION[self::M_SESSION_KEY]);

            session_destroy();
        }

        $this->_data = null;
    }

    public function read($key = null)
    {
        if($key == null)
            return $this->_data;
        
        if (isset($this->_data[$key]))
            return $this->_data[$key];
            
        return null;
    }

    public function write($key, $data)
    {
        $this->_data[$key] = $data;
    }

    public function delete($key)
    {
        if(isset($this->_data[$key]))
        {
            unset($this->_data[$key]);
        }
    }

    public function exists($key)
    {
        return isset($this->_data[$key]);
    }

    public function currentId()
    {
        if($this->_sessionStarted() && $this->_mSessionExists())
            return session_id();

        return null;
    }
}