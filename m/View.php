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

    public function scriptSrc($scriptName)
    {
        $url = $this->homeAddress($scriptName);

        $script = <<< PHREDOC
<script src="$url" type="text/javascript"></script>
PHREDOC;

        return $script;
    }
    
    public function scriptsSrc()
    {
        if(!isset($this->_data['script']))
            return '';

        $scriptsData = $this->_data['script'];
        
        if(is_array($scriptsData))
            $scripts = $scriptsData;
        else
            $scripts = [$scriptsData];
        
        $scriptTags = '';
        
        foreach ($scripts as $script)
        {
            $url = $this->homeAddress($script);

            $scriptTags .= <<< PHREDOC
<script src="$url" type="text/javascript"></script>
PHREDOC;
        }
        
        return $scriptTags;
    }

    public function addScript($script)
    {
        if(!isset($this->_data['script']))
            $this->_data['script'] = array();

        $this->_data['script'][] = $script;
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
    public function setData(array $data)
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

    public function modifyData($index, $value)
    {
        $this->_data[$index] = $value;
    }

    public function appendData(array $newValues)
    {
        foreach ($newValues as $key => $value)
        {
            $this->modifyData($key, $value);
        }
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

    public function sessionData($key = null)
    {
        if(!Session::getInstance()->exists($key))
            return null;

        return Session::getInstance()->read($key);
    }
}