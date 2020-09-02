<?php

namespace m;

use m\extended\AccessControl;

class Application
{
    private $_settings;
    private $_route;
    private $_accessControlPolicies;

    public function __construct(Settings $settings)
    {
        $this->_settings = $settings;
        $this->_route = null;
        $this->_accessControlPolicies = array();
    }

    public function accessControlEnabled()
    {
        return !empty($this->_accessControlPolicies);
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * @return null
     */
    public function getRoute()
    {
        return $this->_route;
    }

    public function enableAccessControl(array $accessPolicies)
    {
        foreach ($accessPolicies as $policy)
            $this->_accessControlPolicies[] = $policy;
    }

    public function run()
    {
        $currentRoute = $this->_settings->currentRoute();

        $controller = $currentRoute->getController();
        $method     = $currentRoute->getMethod();
        $data       = $currentRoute->getData();

        $this->_route = $currentRoute;

        $this->_invokeController($controller, $method, $data);
    }

    private function _invokeController($controllerName, $methodName, $data)
    {
        $controllerName = $controllerName . "Controller";

        $completeControllerName = 'controller\\' . $controllerName;

        $c = new $completeControllerName($this);

        $this->_prepareAccessControl($c);

        $c->$methodName($data);
    }

    private function _prepareAccessControl(Controller $c)
    {
        if($this->accessControlEnabled())
        {
            $accessControl = new AccessControl($c);

            foreach ($this->_accessControlPolicies as $policy)
            {
                $accessControl->addPolicy($policy);
            }

            $c->setAccessControl($accessControl);
        }
    }
}