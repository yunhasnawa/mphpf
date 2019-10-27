<?php

namespace m;

class View
{
    private $_application;
    private $_route;
    private $_settings;
    private $_data;
    private $_baseTemplate;
    private $_contentTemplate;

    public function __construct(Application $application)
    {
        $this->_application = $application;

        $this->_route    = $this->_application->getRoute();
        $this->_settings = $this->_application->getSettings();

        $this->_baseTemplate = $this->_settings->getBaseTemplate();

        $this->_data = array();
        $this->_contentTemplate = '';
    }

    public function homeAddress($prefix = '')
    {
		$protocol = $this->_route->getProtocol() . '://';
		
		$path = $this->_settings->rootURL() . $prefix;
		
		$path = str_replace('//', '/', $path);
		
        return $protocol . $path;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return string
     */
    public function getContentTemplate()
    {
        return $this->_contentTemplate;
    }

    /**
     * @param string $contentTemplate
     */
    public function setContentTemplate($contentTemplate)
    {
        $this->_contentTemplate = $contentTemplate;
    }

    public function renderContent()
    {
        include "template/{$this->_contentTemplate}";
    }

    public function render()
    {
        include "template/{$this->_baseTemplate}";
    }

    public function data($index)
    {
        if(isset($this->_data[$index]))
            return $this->_data[$index];
        else
            return null;
    }

    public function echoData($index, $placeholder = '')
    {
        if(isset($this->_data[$index]))
            echo $this->_data[$index];
        else
            echo $placeholder;
    }
}