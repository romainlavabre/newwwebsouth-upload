<?php
/** @noinspection PhpUndefinedConstantInspection */

namespace Newwebsouth\Upload;


use Newwebsouth\Upload\Exception\UploadDuplicationException;
use Newwebsouth\Upload\Exception\UploadException;
use Newwebsouth\Upload\Exception\UploadSizeException;
use Newwebsouth\Upload\Exception\UploadTypeException;
use Nomess\Components\EntityManager\TransactionObserverInterface;
use Nomess\Components\EntityManager\TransactionSubjectInterface;

class UploadManager implements UploadManagerInterface, TransactionObserverInterface
{
    
    /**
     * @Inject()
     */
    private Configuration $configuration;
    private TransactionSubjectInterface $transactionSubject;
    private array                       $config;
    private array                       $toMove = array();
    
    
    public function __construct( TransactionSubjectInterface $transactionSubject )
    {
        $this->transactionSubject = $transactionSubject;
        $this->subscribeToTransactionStatus();
    }
    
    
    /**
     * @param array $part
     * @param string $configurationName
     * @return array
     * @throws Exception\MissingConfigurationException
     * @throws UploadException
     * @throws UploadTypeException
     * @throws UploadDuplicationException
     */
    public function upload( array $part, string $configurationName ): array
    {
        $this->config = $this->configuration->getConfiguration( $configurationName );
        $this->validConfiguration();
        $this->validSize( $part['size'] );
        $this->validType( $part['name'] );
        
        $name = $this->getName( $part['name'] );
        
        if( $this->mustWaitTransaction() ) {
            $this->toMove[$part['tmp_name']] = $this->config[UploadManagerInterface::PATH] . $name;
        } else {
            $this->move( $part['tmp_name'], $this->config[UploadManagerInterface::PATH] . $name );
        }
        
        return $this->buildMetadata( $name );
    }
    
    
    /**
     * Revalide name of file
     *
     * @param string $tmpName
     * @return string
     * @throws UploadException
     * @throws UploadDuplicationException
     */
    private function getName( string $tmpName ): string
    {
        $duplicationRule = $this->config[UploadManagerInterface::DUPLICATION_RULE];
        
        if( file_exists( $this->config[UploadManagerInterface::PATH] ) . $tmpName ) {
            if( isset( $duplicationRule ) ) {
                
                if( is_callable( $duplicationRule ) ) {
                    return $duplicationRule( $tmpName );
                }
                
                if( $duplicationRule === 'overwrite' ) {
                    return $tmpName;
                }
                
                if( $duplicationRule === 'iteration' ) {
                    $path      = $this->config[UploadManagerInterface::PATH];
                    $iteration = count( scandir( $path ) );
                    $tmp       = $this->decomposeFilename( $tmpName );
                    $name      = $tmp['name'];
                    $extension = $tmp['extension'];
                    
                    for( $i = 1; $i < $iteration; $i++ ) {
                        if( !file_exists( "$path$name($i).$extension" ) ) {
                            return "$name($i).$extension";
                        }
                    }
                }
                
                if( $duplicationRule === 'crash' ) {
                    throw new UploadDuplicationException( 'This file already exists' );
                }
                
                if( !empty( $duplicationRule ) ) {
                    throw new UploadException( "Unknow this duplication rule \"$duplicationRule\"" );
                }
            } else {
                throw new UploadException( 'Not specified rule for duplication name' );
            }
        }
        
        return $tmpName;
    }
    
    
    /**
     * Control that type is valid
     *
     * @param string $name
     * @throws UploadException
     */
    private function validType( string $name ): void
    {
        $isTypeFound = FALSE;
        
        if( !isset( $this->config[UploadManagerInterface::ACCEPT_TYPE] ) ) {
            return;
        }
        
        foreach( $this->config[UploadManagerInterface::ACCEPT_TYPE] as $type ) {
            if( strpos( $name, ".$type" ) !== FALSE ) {
                $isTypeFound = TRUE;
            }
        }
        
        if( !$isTypeFound ) {
            throw new UploadException( 'This type is invalid' );
        }
    }
    
    
    /**
     * Control that file size is valid
     *
     * @param int $size
     * @throws UploadSizeException
     */
    private function validSize( int $size ): void
    {
        if( !isset( $this->config[UploadManagerInterface::MAX_FILE_SIZE] )
            || $this->config[UploadManagerInterface::MAX_FILE_SIZE] === 0 ) {
            return;
        }
        
        if( $this->config[UploadManagerInterface::MAX_FILE_SIZE] < $size ) {
            throw new UploadSizeException( 'File too large' );
        }
    }
    
    
    /**
     * Extract shortname and extension
     *
     * @param string $name
     * @return array
     */
    private function decomposeFilename( string $name ): array
    {
        $tmp       = explode( '.', $name );
        $extension = $tmp[count( $tmp ) - 1];
        unset( $tmp[count( $tmp ) - 1] );
        $name = implode( '.', $tmp );
        
        return [
            'name'      => $name,
            'extension' => $extension
        ];
    }
    
    
    /**
     * Return the file's metadata
     *
     * @param string $filename
     * @return array
     */
    private function buildMetadata( string $filename ): array
    {
        $path = $this->config[UploadManagerInterface::PATH] . $filename;
        
        $array = explode( '.', $filename );
        unset( $array[count( $array ) - 1] );
        
        $nameWithoutExt = implode( '.', $array );
        
        return [
            UploadManagerInterface::META_LOCAL_PATH                   => $path,
            UploadManagerInterface::META_PUBLIC_PATH                  =>
                strpos( $path, ROOT . 'public/' ) !== FALSE
                    ? str_replace( ROOT . 'public', '', $path )
                    : NULL,
            UploadManagerInterface::META_SIZE                         => filesize( $path ),
            UploadManagerInterface::META_SHORT_NAME_WITH_EXTENSION    => $filename,
            UploadManagerInterface::META_SHORT_NAME_WITHOUT_EXTENSION => $nameWithoutExt
        ];
    }
    
    
    private function validConfiguration(): void
    {
        if( !isset( $this->config[UploadManagerInterface::PATH] ) ) {
            throw new UploadException( 'The index "path" is required' );
        }
    }
    
    
    /**
     * Must wait status transactionfor upload
     *
     * @return bool
     */
    private function mustWaitTransaction(): bool
    {
        return isset( $this->config[UploadManagerInterface::WAIT_TRANSACTION] ) && $this->config[self::WAIT_TRANSACTION];
    }
    
    
    /**
     * Move file
     *
     * @param string $tmp
     * @param string $path
     */
    private function move( string $tmp, string $path ): void
    {
        move_uploaded_file( $tmp, $path );
    }
    
    
    /**
     * If transaction validate, upload file
     *
     * @param bool $status
     */
    public function statusTransactionNotified( bool $status ): void
    {
        if( $status ) {
            foreach( $this->toMove as $tmp => $path ) {
                $this->move( $tmp, $path );
            }
        }
    }
    
    
    public function subscribeToTransactionStatus(): void
    {
        $this->transactionSubject->addSubscriber( $this );
    }
}
