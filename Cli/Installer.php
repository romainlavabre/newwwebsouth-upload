<?php


namespace Newwebsouth\Upload\Cli;


use Nomess\Component\Config\ConfigStoreInterface;
use Nomess\Installer\ExecuteInstallInterface;
use Nomess\Installer\NomessInstallerInterface;

/**
 * @author Romain Lavabre <webmaster@newwebsouth.fr>
 */
class Installer implements ExecuteInstallInterface
{
    private const FILENAME = ROOT . 'config/components/upload.yaml';
    private ConfigStoreInterface $configStore;
    
    public function __construct(ConfigStoreInterface $configStore)
    {
        $this->configStore = $configStore;
    }
    
    
    /**
     * @inheritDoc
     */
    public function exec(): void
    {
        copy( __DIR__ . '/upload.yaml', self::FILENAME);
        chown( self::FILENAME, $this->configStore->get( ConfigStoreInterface::DEFAULT_NOMESS)['server']['user']);
    }
}
