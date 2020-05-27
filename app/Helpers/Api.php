<?php

namespace App\Helpers;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\SetBlobPropertiesOptions;
use App\Helpers\AzureStorageService;

Class Api{

	public static function format($status, $data, $ErrorMessage){
			$arr['status']    = !empty($status) ? $status : '';
			$arr['data']      = !empty($data) ? $data : '';
			$arr['error_msg'] = !empty($ErrorMessage) ? $ErrorMessage : '';
			return $arr;
	}
	public static function uploadBlob($Container = null, $newFileName, $Content){
		try {
			if(filter_var($Content, FILTER_VALIDATE_URL)){
				return $Content;
			}elseif (preg_match('/^data:image\/(\w+);base64,/', $Content, $Extension)) {
				$Content   = substr($Content, strpos($Content, ',') + 1);
				$Extension = strtolower($Extension[1]);
				$Content   = base64_decode($Content);

				if ($Content === false) 
					throw new \Exception('base64_decode failed');

			}else {
				throw new \Exception('did not match data URI with image data');
			}
				
			$Endpoint 	 = env('BLOB_DEFAULT_ENDPOINTS_PROTOCOL');
			$AccountName = env('BLOB_ACCOUNT_NAME');
			$AccountKey  = env('BLOB_ACCOUNT_KEY');
			$Container   = empty($Container) ? env('BLOB_CONTAINER') : $Container;

			$ConnectionString  = "DefaultEndpointsProtocol=".$Endpoint;
			$ConnectionString .= ";AccountName=".$AccountName;
			$ConnectionString .= ";AccountKey=".$AccountKey;

			$BlobClient  = BlobRestProxy::createBlobService($ConnectionString);
			$NewFileName = $newFileName.'.'.$Extension;
			
			$result = AzureStorageService::uploadBlobStorage($BlobClient, $Container, $Content, $NewFileName, 'string');
			if($result) return "$Endpoint://$AccountName.blob.core.windows.net/$Container/$NewFileName";
		} catch (ServiceException $e) {
			throw new \Exception($e->getMessage().PHP_EOL, 500);
		}
	}

	public static function uploadBlobReceipt($ReservationId, $Content, $container, $extension){
		try {
			$Endpoint = env('BLOB_DEFAULT_ENDPOINTS_PROTOCOL');
			$AccountName = env('BLOB_ACCOUNT_NAME');
			$AccountKey  = env('BLOB_ACCOUNT_KEY');

			$ConnectionString  = "DefaultEndpointsProtocol=".$Endpoint;
			$ConnectionString .= ";AccountName=".$AccountName;
			$ConnectionString .= ";AccountKey=".$AccountKey;

			$BlobClient  = BlobRestProxy::createBlobService($ConnectionString);
			$NewFileName = $ReservationId . '.' . $extension;

			$result = AzureStorageService::uploadBlobStorage($BlobClient, $container, $Content, $NewFileName, 'string');
			if($result) return "$Endpoint://$AccountName.blob.core.windows.net/$container/$NewFileName";
		} catch (ServiceException $e) {
			throw new \Exception($e->getMessage().PHP_EOL, 500);
		}
	}

}
