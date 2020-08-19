<?php /** @noinspection PhpUndefinedConstantInspection */


namespace Newwebsouth\Upload;


use Newwebsouth\Upload\Exception\MissingConfigurationException;

class Configuration
{
    private const PATH_CONFIGURATION = ROOT . 'config/components/Upload.php';
    private array $config;
    
    /**
     *
     * * @throws MissingConfigurationException
     */
    public function __construct()
    {
        if(!file_exists(self::PATH_CONFIGURATION)){
            throw new MissingConfigurationException('The components upload must have a file config in config/components/Upload.php');
        }
        
        $this->config = require self::PATH_CONFIGURATION;
        
    }
    
    
    public function getConfiguration(string $name): array
    {
        if(!array_key_exists($name, $this->config)){
            throw new MissingConfigurationException("The configuration with name \"$name\" was not found");
        }
        
        return $this->config[$name];
    }
}
