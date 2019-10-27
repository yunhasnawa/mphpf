<?php

namespace m;

class Application
{
    private $_settings;
    private $_route;

    public function __construct(Settings $settings)
    {
        $this->_settings = $settings;
        $this->_route = null;
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
        $c->$methodName($data);
    }
}