<?php


namespace m;


class Route
{
    private $_settings;
    private $_rootURL;
    private $_routeMetadata;
    private $_protocol;

    private $_rawURL;
    private $_path;
    private $_controller;
    private $_method;
    private $_data;

    // Parameter-related
    private $_hasParams;
    private $_rawParams;
    private $_pathWithoutParams;

    public function __construct(Settings $settings)
    {
        $this->_settings = $settings;

        $this->_hasParams = false;
        $this->_rawParams = null;

        $this->_rootURL = $this->_settings->rootURL();
        $this->_rawURL = $this->_settings->currentURL();
        $this->_routeMetadata = $this->_settings->getRoute();

        $this->_pathWithoutParams = null;

        $this->_protocol = self::_currentProtocol(false);
    }

    private static function _currentProtocol($appendSlashes = true)
    {
        $slashes = $appendSlashes ? '://' :'';

        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https$slashes" : "http$slashes";
    }

    public function construct()
    {
		//pre_print($this->_rootURL . "<----->" . $this->_rawURL);
		// Hilangkan dulu root url, sehingga bisa dibandingkan path ke controller-nya.
        $this->_path = str_replace($this->_rootURL,'', $this->_rawURL);

        //pre_print($this->_path);
		
		if(empty($this->_path))
			$this->_path = '/';

		// Tambahkan '/' kalau awalnya belum ada karakter tersebut.
		if($this->_path[0] !== '/')
			$this->_path = "/{$this->_path}";

		// Ini yang dicari!
        $searchPath = $this->_path;

        //echo "Checking path: $searchPath<br/>";

        $found = null;

        $max = count(explode('/', $searchPath));
        //echo "$max<br/>";

        // Check for '?'
        $stripParams = $this->_handleParameters($searchPath);

        if($stripParams !== null)
        {
            $this->_pathWithoutParams = $stripParams;

            $searchPath = $this->_pathWithoutParams;
        }

        for($i = 0; $i < $max; $i++)
        {
            $found = self::_search($searchPath);

            if($found === null)
                $searchPath = self::_slicePath($searchPath, $i);
            else
                break;
        }

        if($found === null)
            return false;


        $this->_controller = $found[1];
        $this->_method = $found[2];
        $this->_data = self::_extractData($this->_path, $searchPath);

        return true;
    }

    /**
     * @return bool
     */
    public function hasParams()
    {
        return $this->_hasParams;
    }

    /**
     * @return null
     */
    public function getRawParams()
    {
        return $this->_rawParams;
    }


    private function _handleParameters($path)
    {
        $expl = explode('?', $path);

        if(count($expl) >= 2)
        {
            $this->_hasParams = true;

            $pathOnly = $expl[0];

            $this->_rawParams = $expl[1];

            return $pathOnly;
        }

        return null; // Does not have any parameter
    }

    private static function _extractData($originalPath, $metadataPath)
    {
        if($originalPath === $metadataPath)
            return null;

        $lastCharIndex = (strlen($metadataPath) - 1);

        $lastChar = $metadataPath[$lastCharIndex];

        //echo "$originalPath  <-->  $metadataPath [$lastChar]<br/>";

        if($lastChar === '*')
            $metadataPath = substr($metadataPath, 0, $lastCharIndex);

        //echo "$originalPath  <-->  $metadataPath [$lastChar]<br/>";

        $dataStr = str_replace($metadataPath, '', $originalPath);

        //echo "Data = $dataStr<br/>";

        return $dataStr;
    }

    private function _search($path)
    {
        foreach($this->_routeMetadata as $r)
        {
            //pre_print("{$this->_path}  <-->  {$r[0]}<br/>");

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

    /**
     * @return mixed
     */
    public function getRawURL()
    {
        return $this->_rawURL;
    }

    /**
     * @param bool $withParams Returns URL with its param or not e.g. http://test.com?q=coba
     * @return mixed
     */
    public function getPath($withParams = true)
    {
        if($withParams)
            return $this->_path;
        else
            if(!$this->hasParams())
                return $this->_path;
            else
                return $this->_pathWithoutParams;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->_protocol;
    }

    public function toURL($next = '')
    {
        $path = $this->getPath(false);

        $protocol = self::_currentProtocol(true);

        return $protocol . $this->_rootURL . $path . $next;
    }
}