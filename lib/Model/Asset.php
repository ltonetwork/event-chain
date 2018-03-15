<?php

/**
 * An asset is a file that is available both on the filesystem and throught HTTP.
 */
class Asset implements JsonSerializable
{
    /** @var string */
    protected $path;
    
    /** @var string */
    protected $url;
    
    
    /**
     * Class constructor
     * 
     * @param type $path  Path to the file
     * @param type $url   Url to the file, automatically determined if omited 
     */
    public function __construct($path, $url=null)
    {
        $this->path = $path;
        $this->url = $url ?: $this->determineUrl($path);
    }
    
    /**
     * Determine the URL base on the path
     * 
     * @param string $path
     * @return string
     */
    protected function determineUrl($path)
    {
        if (preg_match('~^https?://~', $path)) return $path;
        
        if (substr($path, 0, strlen(BASE_PATH) + 1) == BASE_PATH . '/') {
            list($base, $dir) = explode('/', substr($path, strlen(BASE_PATH) + 1), 2);
            return ($base == 'webroot' ? '' : App::url($base)) . '/' . $dir;
        }

        return null;
    }
    
    
    /**
     * Get the path to the file
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Get the url
     * 
     * @param boolean $absolute
     * @return string
     */
    public function getUrl($absolute=false)
    {
        if ($absolute && $absolute !== 'relative' && isset($this->url) && !parse_url($this->url, PHP_URL_SCHEME))
            return ($_SERVER['HTTPS'] ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $this->url;
        
        return $this->url;
    }
    
    
    /**
     * Check if file exists
     * 
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->path);
    }
    
    
    /**
     * Perform a glob an get matching assets
     * 
     * @param string $pattern
     * @return Asset[]
     */
    public function glob($pattern)
    {
        if (!is_dir($this->path)) return null;
        
        $files = glob($this->path . "/$pattern", GLOB_BRACE);
        if (empty($files)) return null;

        usort($files, function($a, $b) { return filemtime($a) - filemtime($b); });

        foreach ($files as &$file) {
            $file = new Asset($file, $this->url . substr($file, strlen($this->path)));
        }
        
        return $files;
    }
    
    
    /**
     * Return an assest for this file with a prefix before the filename.
     * This is useful for thumbnail generator or add extra subdir.
     * 
     * @param string $prefix
     * @return Asset
     */
    public function withPrefix($prefix)
    {
        $asset = new self(
            dirname($this->path) . "/$prefix" . basename($this->path),
            dirname($this->url) . "/$prefix" . basename($this->url)
        );
        
        foreach ($this as $property=>$value) {
            if ($property == 'path' || $property == 'url') continue;
            $asset->$property = $value;
        }
        
        return $asset;
    }
    
    /**
     * Cast asset to url for json encode
     * 
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->getUrl(true);
    }
}
