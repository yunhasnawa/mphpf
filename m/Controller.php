<?php

namespace m;

use m\extended\AccessControl;

class Controller
{
    protected $view;

    private $_application;
    private $_accessControl;

    public function __construct(Application $application)
    {
        $this->_application = $application;

        $this->view = new View($this->_application);

        $this->_accessControl = null;
    }

    protected static function rootDir($additional = '')
    {
        $absDir =  dirname(__FILE__);

        $rootDir = Util::strRemoveLastSegment('/', $absDir, true);

        return "{$rootDir}{$additional}";
    }

    public function setAccessControl(AccessControl $accessControl)
    {
        //echo "Setting access control<br/>";

        $this->_accessControl = $accessControl;
    }

    public function accessControl()
    {
        if($this->_accessControl == null)
            die("[M::ERROR] Currently, Access Control is not available. It may be due to requesting inspection while Access Control is not enabled or trying to get it from the constructor of a controller.");

        /*
        echo '<pre>';
        print_r($this->_accessControl->getPolicies());
        echo '</pre>';
        */

        return $this->_accessControl;
    }

    public function redirect($route, array $uriDataAssoc = [])
    {
        $uri = '';

        if(!empty($uriDataAssoc))
        {
            $uri .= '?';

            foreach ($uriDataAssoc as $key => $value)
            {
                $uri .= "{$key}={$value}&";
            }

            $uri = Util::strRemoveLastChars($uri);
        }

        $location = $this->view->homeAddress($route . $uri);

        header("Location: " . $location);
    }

    public function homeAddress($additional = '')
    {
        return $this->view->homeAddress($additional);
    }

    protected function application()
    {
        return $this->_application;
    }

    public function getCurrentRoute()
    {
        return $this->application()->getRoute();
    }
}