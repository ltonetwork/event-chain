<?php

/**
 * Trait for records that have images
 */
trait AttachImages
{
    /**
     * List with path or url of images
     * @var array
     */
    protected $_images = [];
    
    /**
     * Path or url of main image
     * @var string
     */
    protected $_main_image;
    
    
    /**
     * Get path to images
     * 
     * @return Asset
     */
    public function getImageDir()
    {
        if ($this->getId() === null) return null;
        
        $path = BASE_PATH . '/static/' . $this->getDBTable() . '/' . sprintf('%03d', $this->id % 1000) . '/'
            . sprintf('%08d', $this->id);
        
        return new Asset($path);
    }
    
    /**
     * Get the filename of the selected image
     * 
     * @return string
     */
    protected function getImageFilename()
    {
        return 'image';
    }
    
    /**
     * Save all images
     * 
     * @param array  $images
     * @param string $main_image
     * @return \Jasny\DB\Record $this
     */
    public function addImages($images, $main_image=null)
    {
        $this->_images = array_merge($this->_images, $images);
        
        if ($main_image === true) $main_image = $this->getImage() ? null : reset($images);
        if (isset($main_image)) $this->_main_image = $main_image;
        
        return $this;
    }
    
    /**
     * Save added images
     * 
     * @return \Jasny\DB\Record $this
     */
    protected function saveImages()
    {
        $dir = $this->getImageDir()->getPath();
        if (!file_exists($dir)) mkdir($dir, 0775, true);
        
        $tmpdir = BASE_PATH . '/cache/tmp';
        if (!file_exists($tmpdir)) mkdir($tmpdir, 0777);
        
        $files = [];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        
        // Save images
        foreach ($this->_images as $image) {
            $tmpfile = $tmpdir .'/' . uniqid();
            if (!copy($image, $tmpfile)) continue;

            list($type, $ext) = explode('/', strtolower($finfo->file($tmpfile))) ?: array('unknown', 'unknown');
            if ($type != 'image') {
                trigger_error("File '$image' is a $type/$ext, not an image", E_USER_WARNING);
                unlink($tmpfile);
                continue;
            }
            if ($ext === 'jpeg') $ext = 'jpg';
            
            $file = md5_file($tmpfile) . ".$ext";
            rename($tmpfile, "$dir/$file");
            $files[$image] = $file;
        }
        
        // Set main image
        if (isset($this->_main_image)) {
            $image = isset($files[$this->_main_image]) ? $files[$this->_main_image] : $this->_main_image;

            list($current) = $this->getImageDir()->glob($this->getImageFilename() . '.{jpg,gif,png}');
            if ($current && is_link($current->getPath())) unlink($current->getPath());
        
            $target = basename($image);
            $link = $this->getImageFilename() . '.' . pathinfo($target, PATHINFO_EXTENSION);

            symlink($target, $this->getImageDir()->getPath() . '/' . $link);
            var_dump($target, $this->getImageDir()->getPath() . '/' . $link);
        }
        
        $this->_images = [];
        $this->_main_image = null;
        
        return $this;
    }
    
    /**
     * Get all images
     * 
     * @return string $size
     * @return Asset[]
     */
    public function getImages($size=null)
    {
        if (!$this->getImageDir()) return [];

        $assets = $this->getImageDir()->glob('*.{jpg,gif,png}');
        $selected = !$this->_main_image && $this->getImage() ? readlink($this->getImage()->getPath()) : null;
        
        foreach ($assets as $i=>$asset) {
            if (pathinfo($asset->getPath(), PATHINFO_FILENAME) == $this->getImageFilename()) unset($assets[$i]);
            if (basename($asset->getPath()) === $selected) $asset->selected = true;
        }
        
        if (isset($size)) {
            foreach ($assets as &$asset) $asset = $asset->withPrefix("$size.");
        }
        
        foreach ($this->_images as $image) {
            $assets[] = new Asset($image);
            if ($this->_main_image === $image) end($assets)->selected = true;
        }
        
        return $assets;
    }
    
    /**
     * Get main image
     * 
     * @return string $size
     * @return Asset
     */
    public function getImage($size=null)
    {
        if (isset($this->_main_image)) return new Asset($this->_main_image);
        
        if (!$this->getImageDir()) return null;

        list($asset) = $this->getImageDir()->glob($this->getImageFilename() . '.{jpg,gif,png}');
        return isset($size) && isset($asset) ? $asset->withPrefix("$size.") : $asset;
    }
    
    /**
     * Select main image
     * 
     * @param string $image
     * @return Person|Organization $this
     */
    public function selectImage($image)
    {
        $this->_main_image = $image;
        return $this;
    }
    
    /**
     * Save record to DB
     * 
     * @return \Jasny\DB\Record $this
     */
    public function save()
    {
        parent::save();
        $this->saveImages();
        
        return $this;
    }
}
