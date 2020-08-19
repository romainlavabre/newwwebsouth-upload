# Upload Manager

The upload Manager help you for upload a file.

Configuration:

> config/components/Upload.php

<code>
return [ <br>
    &nbsp;&nbsp;&nbsp;&nbsp;'config_name' => [ <br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::PATH => '/var/sample/dir/',<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::MAX_FILE_SIZE => 100000,<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::ACCEPT_TYPE => [<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'type_1',<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'type_2',<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;],<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::DUPLICATION_RULE => 'closure|string : iteration| string : overwrite| string : crash'<br> 
    &nbsp;&nbsp;&nbsp;&nbsp;]<br>
]
</code>

For Duplication rule

*Closure:* function(string $originalName){ ... }

*"iteration":* A number will be added to filename:

> test.png = test(1).png 

*"overwrite":* The file will be overwriting

*"crash":* You will receive a `Newwebsouth\Upload\Exception\UploadDuplicationException::class`

Mappe the interface in container configuration

### Method

> upload(array $part, string $configurationName): array


return a metadata of document:

<code>
return [<br>
    &nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::META_LOCAL_PATH  => $path,<br>
    &nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::META_PUBLIC_PATH => public path (if is public ressource),<br>
    &nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::META_SIZE => size of document,<br>
    &nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::META_SHORT_NAME_WITH_EXTENSION    => filename with extension,<br>
    &nbsp;&nbsp;&nbsp;&nbsp;UploadManagerInterface::META_SHORT_NAME_WITHOUT_EXTENSION => filename without extension<br>
]
</code>
