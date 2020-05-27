<?php

namespace App\Helpers;

use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

Class AzureStorageService{

    /**
     * Type :
     * file and string
     */
    public static function uploadBlobStorage($blobClient, $container, $sourceFile, $newFileName, $type=null){
        try {
            if($type == 'string'){
                $content = $sourceFile;
            }else{
                if (!file_exists($sourceFile)) throw new \Exception('File does not exist.', 500);
                $content = fopen($sourceFile, "r");
            }
           
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $sourceFile, FILEINFO_MIME_TYPE);

            $options = new CreateBlockBlobOptions();
            $options->setContentType($mime_type);

            //Upload blob
            $blobClient->createBlockBlob($container, $newFileName, $content, $options);
            return true;
        } catch (ServiceException $e) {
            throw new \Exception($e->getMessage().PHP_EOL, 500);
        }
    }

    public static function downloadBlobStorage($blobClient, $container, $fileName){
        // Create blob REST proxy.
        $blobRestProxy = $blobClient;
        $ext = new \SplFileInfo(basename($fileName));
        $fileext = strtolower($ext->getExtension());
        try {
            // Get blob.
            $blob = $blobRestProxy->getBlob($container, $fileName);

            if($fileext === "pdf") {
                header('Content-type: application/pdf');
            } else if ($fileext === "doc") {
                header('Content-type: application/msword'); 
            } else if ($fileext === "docx") {
                header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); 
            } else if($fileext === "txt") {
                header('Content-type: plain/text');
            }
            header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
            fpassthru($blob->getContentStream());
            return true;
        }catch(ServiceException $e){
            throw new \Exception($e->getMessage().PHP_EOL, 500);
        }
    
    }

}