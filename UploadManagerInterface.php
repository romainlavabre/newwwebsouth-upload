<?php


namespace Newwebsouth\Upload;


use Newwebsouth\Upload\Exception\UploadDuplicationException;
use Newwebsouth\Upload\Exception\UploadException;
use Newwebsouth\Upload\Exception\UploadTypeException;

interface UploadManagerInterface
{
    
    public const PATH             = 'path';
    public const MAX_FILE_SIZE    = 'max_file_size';
    public const DUPLICATION_RULE = 'duplication_rule';
    public const ACCEPT_TYPE      = 'accept_type';
    public const WAIT_TRANSACTION = 'wait_transaction';
    public const META_LOCAL_PATH                   = 'local_path';
    public const META_PUBLIC_PATH                  = 'public_path';
    public const META_SIZE                         = 'size';
    public const META_TYPE                         = 'type';
    public const META_SHORT_NAME_WITH_EXTENSION    = 'shortname';
    public const META_SHORT_NAME_WITHOUT_EXTENSION = 'shortname_without_ext';
    
    
    /**
     * @param array $part
     * @param string $configurationName
     * @return array
     * @throws Exception\MissingConfigurationException
     * @throws UploadException
     * @throws UploadTypeException
     * @throws UploadDuplicationException
     */
    public function upload( array $part, string $configurationName ): array;
}
