<?php /** @noinspection PhpUndefinedConstantInspection */


namespace Newwebsouth\Upload;


use Newwebsouth\Upload\Exception\MissingConfigurationException;
use Nomess\Component\Cache\CacheHandlerInterface;
use Nomess\Component\Config\ConfigStoreInterface;

class Configuration
{
    private const CONF_NAME = 'upload';
    private array $config;
    
    /**
     *
     * * @throws MissingConfigurationException
     */
    public function __construct(ConfigStoreInterface $configStore)
    {
        
        $this->config = $configStore->get(self::CONF_NAME);
        
    }
    
    
    public function getConfiguration(string $name): array
    {
        if(!array_key_exists($name, $this->config)){
            throw new MissingConfigurationException("The configuration with name \"$name\" was not found");
        }
        
        return $this->config[$name];
    }
}
