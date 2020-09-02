<?php


namespace m\extended;


use m\Controller;

class AuthPolicy implements Policy
{
    private $_authModel;

    public function __construct(AuthModel $authModel)
    {
        $this->_authModel = $authModel;
    }

    public function inspect(Controller $subject)
    {
        if($this->_authModel->grantAccess($subject))
            return true;

        return false;
    }

    public function getRedirectRoute()
    {
        return $this->_authModel->getRedirectRoute();
    }

    public function getModel()
    {
        return $this->_authModel;
    }
}