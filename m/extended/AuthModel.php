<?php

namespace m\extended;

use m\Controller;
use m\Model;
use m\Session;

abstract class AuthModel extends Model
{
    const SESSION_KEY_AUTH_USERNAME = 'auth_username';

    private $_session;

    public function __construct($tableName)
    {
        parent::__construct($tableName);

        $this->_session = Session::getInstance();
    }

    public function sessionUsername()
    {
        return $this->_session->read(self::SESSION_KEY_AUTH_USERNAME);
    }

    public function sessionStore($username)
    {
        $this->_session->write(self::SESSION_KEY_AUTH_USERNAME, $username);
    }

    public function sessionClear()
    {
        $this->_session->end();
    }

    protected function getSession()
    {
        return $this->_session;
    }

    public abstract function getUser($username, $password);
    public abstract function getRedirectRoute();
    public abstract function grantAccess(Controller $controller);

}