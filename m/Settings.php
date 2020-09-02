<?php

namespace m;

class Settings
{
    private static $_instance;

    private $_appFolder;
    private $_route;
    private $_baseTemplate;
    private $_dbConnection;

    // Read only properties
    private $_absoluteAppFolder;

    /**
     * @return string
     */
    public function getAbsoluteAppFolder()
    {
        return $this->_absoluteAppFolder;
    }

    private function __construct()
    {
        $this->_appFolder = self::_detectAppFolder();
        $this->_absoluteAppFolder = self::_detectAbsoluteAppFolder();
        $this->_route = array();
        $this->_baseTemplate = 'base_template.php';
        $this->_dbConnection = array(
			'dbms'         => 'mysql',
            'server'       => 'localhost',
            'database'     => '',
            'username'     => '',
            'password'     => '',
            'die_on_error' => false
        );
    }

    private static function _detectAppFolder()
    {
        $fileDirname = dirname(__FILE__);

        // TODO: Monitor this change. Might be broken someday
        // Change \ to / for Windows OS
        $fileDirname = str_replace('\\', '/', $fileDirname);

        $segments = explode('/', $fileDirname);

        if(count($segments) < 2)
            return '/';

        $appFolderSegments = (count($segments) - 2);

        $appFolder = "/{$segments[$appFolderSegments]}";

        return $appFolder;
    }

    private static function _detectAbsoluteAppFolder()
    {
        $fileDirname = dirname(__FILE__);

        $segments = explode('/', $fileDirname);

        $lastSegmentIndex = count($segments) - 1;

        if($lastSegmentIndex < 1)
            return '/';

        unset($segments[$lastSegmentIndex]);

        $absolutePath = implode('/', $segments);

        return $absolutePath;
    }

    public static function getInstance()
    {
        if(Settings::$_instance == null)
        {
            Settings::$_instance = new Settings();
        }

        return Settings::$_instance;
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return $this->_route;
    }

    /**
     * @param array $route
     */
    public function setRoute(array $route)
    {
        $this->_route = $route;
    }

    /**
     * App Folder will be detectef automatically if this functions doesn't get called
     * @return string
     */
    public function getAppFolder()
    {
        return $this->_appFolder;
    }

    /**
     * @param string $appFolder
     * @return void
     */
    public function setAppFolder($appFolder)
    {
		if(empty($appFolder))
		{
            $this->_appFolder = '/';

            return;
        }
		
        $firstChar = $appFolder[0];

        $appFolder = $firstChar === '/' ? $appFolder : "/$appFolder";

        $this->_appFolder = $appFolder;
    }

    /**
     * @return string
     */
    public function getBaseTemplate()
    {
        return $this->_baseTemplate;
    }

    /**
     * @param string $baseTemplate
     */
    public function setBaseTemplate($baseTemplate)
    {
        $this->_baseTemplate = $baseTemplate;
    }

    /**
     * @return array
     */
    public function getDbConnection()
    {
        return $this->_dbConnection;
    }

    /**
     * @param array $dbConnection
     */
    public function setDbConnection($dbConnection)
    {
        foreach ($dbConnection as $key => $value)
        {
            if (isset($this->_dbConnection[$key]))
                $this->_dbConnection[$key] = $value;
        }
    }

    public function rootURL()
    {
        $rootURL = $_SERVER['SERVER_NAME'] . $this->_appFolder;

        //pre_print($rootURL);

        return $rootURL;
    }

    public function currentURL()
    {
        $rootURL = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        return $rootURL;
    }

    public function currentRoute()
    {
        $r = new Route($this);

        $canConstruct = $r->construct();

        if(!$canConstruct)
            die("[M::ERROR] Cannot find requested route for path {$r->getPath()}");

        return $r;
    }

    public function currentRouteOld()
    {
        $path = str_replace($this->rootURL(),'', $this->currentURL());

        echo "Checking path: $path<br/>";

        $search = null;

        $max = count(explode('/', $path));
        echo "$max<br/>";

        for($i = 0; $i < $max; $i++)
        {
            $search = self::_findRoute($path);

            if($search === null)
                $path = self::_slicePath($path, $i);
            else
            {
                $path = $search;
                break;
            }
        }

        if($search === null)
            die("Cannot find requested route for path $path");

        return $search;
    }

    private function _findRoute($path)
    {
        foreach($this->_route as $r)
        {
            echo "$path  <-->  {$r[0]}<br/>";
            if($path === $r[0])
            {
                return $r;
            }
        }

        return null;
    }

    private static function _slicePath($path, $popCount, $append = '*')
    {
        if($popCount < 1)
            return $path;

        $split = explode('/', $path);

        if($popCount >= count($split))
            return '';

        // a/b/c/d <-- delete as many as $popCount from behind
        // a/b/c   <-- Remove from index = count - $popCount
        for ($i = 0; $i < $popCount; $i++)
        {
            $delIndex = count($split) - 1;

            unset($split[$delIndex]);
        }

        $rejoin = implode('/', $split);

        $rejoin .= "/$append";

        return $rejoin;
    }

    public function displayError()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
}