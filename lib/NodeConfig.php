<?php

use Symfony\Component\Yaml\Yaml as Symfony_Yaml;

/**
 * Configure a node
 */
class NodeConfig extends Jasny\Config
{
    /**
     * @var string
     */
    private $_source;
    
    /**
     * Class constructor.
     * 
     * @param string $source
     * @param array  $options
     */
    public function __construct($source = null, $options = [])
    {
        if (!isset($source)) {
            throw new UnexpectedValueException("Please provide a source of the node yaml file");
        }
        
        $options['loader'] = 'yaml';
        
        parent::__construct($source, $options);
    }
    
    
    /**
     * Get the private key of the node
     * 
     * @param string $type  'sign' or 'decrypt'
     */
    public function getPrivateKey($type)
    {
        $privateFile = preg_replace('/\.yml/', '-private.yml');
        
        $private = new Config($privateFile);
        
        return (isset($private->$type) ? $private->$type : null);
    }
    
    
    
    /**
     * Seet the public / private key pairs.
     * 
     * @param string $seedText
     */
    public function seed($seedText)
    {
        
        file_put_contents($this->_source, Symfony_Yaml::dump($settings));
        
        $privateFile = preg_replace('/\.yml/', '-private.yml');
        file_put_contents($privateFile, Symfony_Yaml::dump($privateSettings));
    }
}
