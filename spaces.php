<?php
require_once(dirname(__FILE__)."/aws/autoloader.php");

class SpacesConnect {
  /*
  An API wrapper for AWS, makes working with DigitalOcean's Spaces super easy.
  Written by Devang Srivastava for Dev Uncoded.
  Available under MIT License ( https://opensource.org/licenses/MIT )
  */

    function __construct($access_key, $secret_key, $spaceName = "", $region = "nyc3", $host = "digitaloceanspaces.com") {
        if(!empty($spaceName)) {
          $endpoint = "https://".$spaceName.".".$region.".".$host;
        }
        else {
          $endpoint = "https://".$region.".".$host;
        }
        try {
          $this->client = Aws\S3\S3Client::factory(array(
            'region' => $region,
            'version' => 'latest',
            'endpoint' => $endpoint,
            'credentials' => array(
                      'key'    => $access_key,
                      'secret' => $secret_key,
                  ),
            'bucket_endpoint' => true
          ));
        } catch (\Exception $e) {
          $this->HandleAWSException($e);
        }
        $this->space = $spaceName;
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
        $this->host = $host;
        $this->region = $region;
    }



    function createSpace($spaceName, $region = "") {
        if(empty($region)) {
          $region = $this->region;
        }
        $current_space = $this->space;
        try {
          $this->setSpace($spaceName);
          $success = $this->client->createBucket(array('Bucket' => $spaceName));
          $this->client->waitUntil('BucketExists', array('Bucket' => $spaceName));
          $this->setSpace($current_space);
          return $this->ObjReturn($success->toArray());
        } catch (\Exception $e) {
          $this->HandleAWSException($e);
        }
    }

    function listSpaces() {
        $current_space = $this->space;
        try {
          $this->setSpace(NULL);
          $spaceList = $this->client->listBuckets();
          $this->setSpace($current_space);
          return $this->ObjReturn($spaceList->toArray());
        } catch (\Exception $e) {
          $this->HandleAWSException($e);
        }
    }

    function ChangeSpace($spaceName, $region = "", $host = "") {
      return setSpace($spaceName, $region, $host);
    }

    function setSpace($spaceName, $region = "", $host = "") {
        if(empty($region)) { $region = $this->region; }
        if(empty($host)) { $host = $this->host; }
        if(!empty($spaceName)) {
          $endpoint = "https://".$spaceName.".".$region.".".$host;
          $this->space = $spaceName;
        } else {
          $endpoint = "https://".$region.".".$host;
          $this->space = "";
        }
        try {
          $this->client = Aws\S3\S3Client::factory(array(
            'region' => $region,
            'version' => 'latest',
            'endpoint' => $endpoint,
            'credentials' => array(
                      'key'    => $this->access_key,
                      'secret' => $this->secret_key,
                  ),
            'bucket_endpoint' => true
          ));
          return $this->ObjReturn(true);
        } catch (\Exception $e) {
          $this->HandleAWSException($e);
        }
    }

    function getSpaceName() {
        return $this->ObjReturn($this->space);
    }

    function downloadSpaceToDirectory($pathToDirectory) {
      try {
        $this->client->downloadBucket($pathToDirectory, $this->space);
        return $this->ObjReturn(true);
      } catch (\Exception $e) {
        $this->HandleAWSException($e);
      }
    }

    function destroyThisSpace() {
        try {
          $objects = $this->listObjects();
          foreach ($objects as $value) {
            $key = $value["Key"];
            $this->deleteObject($key);
          }
          $this->client->deleteBucket(array('Bucket' => $this->space));
          $this->client->waitUntil('BucketNotExists', array('Bucket' => $this->space));
         return $this->ObjReturn(true);
         }
         catch (\Exception $e) {
          $this->HandleAWSException($e);
         }
    }





    function listObjects() {
      try {
         $objects = $this->client->getIterator('ListObjects', array(
             'Bucket' => $this->space
         ));
         $objectArray = array();
         foreach ($objects as $object) {
           $objectArray[] = $object;
         }
         return $this->ObjReturn($objectArray);
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function doesObjectExist($objectName) {
      try {
         return $this->ObjReturn($this->client->doesObjectExist($this->space, $objectName));
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function getObject($file_name = "") {
      try {
        $result = $this->client->getObject([
          'Bucket' => $this->space,
          'Key' => $file_name,
         ]);
         return $this->ObjReturn($result->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function makePrivate($file_path = "") {
      try {
        return $this->PutObjectACL($file_path, ["ACL" => "private"]);
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function makePublic($file_path = "") {
      try {
        return $this->PutObjectACL($file_path, ["ACL" => "public-read"]);
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function deleteObject($file_path = "") {
      try {
        return $this->ObjReturn($this->client->deleteObject([
        'Bucket' => $this->space,
        'Key' => $file_path,
        ])->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function uploadFile($pathToFile, $access = "private", $fileName = "") {
        if(empty($fileName)) {
          $fileName = $pathToFile;
        }
        if($access == "public") {
          $access = "public-read";
        }
        try {
          $result = $this->client->putObject(array(
              'Bucket'  => $this->space,
              'Key'     => $fileName,
              'Body'    => fopen($pathToFile, 'r+'),
              "ACL"     => $access
          ));

          $this->client->waitUntil('ObjectExists', array(
              'Bucket' => $this->space,
              'Key'    => $fileName
          ));

          return $this->ObjReturn($result->toArray());
         }
         catch (\Exception $e) {
          $this->HandleAWSException($e);
         }
    }

    function downloadFile($fileName, $destinationPath) {
      try {
        $result = $this->client->getObject(array(
            'Bucket' => $this->space,
            'Key'    => $fileName,
            'SaveAs' => $destinationPath
        ));

        return $this->ObjReturn($result->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function uploadDirectory($directory, $keyPrefix = "") {
      try {
        $this->client->uploadDirectory($directory, $this->space, $keyPrefix);

        return $this->ObjReturn($result->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }






    function listCORS() {
      try {
        $cors = $this->client->getBucketCors([
          'Bucket' => $this->space,
         ]);
         return $this->ObjReturn($cors->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function putCORS($cors_rules = "") {
      if(empty($cors_rules)) {
        $cors_rules = [
         'AllowedMethods' => ['GET'],
         'AllowedOrigins' => ['*'],
         'ExposeHeaders' => ['Access-Control-Allow-Origin'],
         ];
        }
        try {
          $result = $this->client->putBucketCors([
            'Bucket' => $this->space,
            'CORSConfiguration' => ['CORSRules' => [$cors_rules]]
          ]);
          return $this->ObjReturn($result->toArray());
         }
         catch (\Exception $e) {
          $this->HandleAWSException($e);
         }
    }

    function listSpaceACL() {
      try {
        $acl = $this->client->getBucketAcl([
          'Bucket' => $this->space,
         ]);
        return $this->ObjReturn($acl->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }


    function PutSpaceACL($params) {
      try {
        $acl = $this->client->putBucketAcl($params);
        return $this->ObjReturn($acl->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function listObjectACL($file) {
      try {
        $result = $this->client->getObjectAcl([
           'Bucket' => $this->space,
           'Key' => $file,
        ]);
        return $this->ObjReturn($result->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }

    function PutObjectACL($file, $acl) {
      try {
        $acl = array_merge(array("Bucket" => $this->space, "Key" => $file), $acl);
        $result = $this->client->putObjectAcl($acl);
        return $this->ObjReturn($result->toArray());
       }
       catch (\Exception $e) {
        $this->HandleAWSException($e);
       }
    }




    function CreateTemporaryURL($file_name = "", $valid_for = "1 hour") {
        $secret_key = $this->secret_key;
        $expiry = strtotime("+ ".$valid_for);
        $file_name = rawurlencode($file_name);
        $file = str_replace(array('%2F', '%2B'), array('/', '+'), ltrim($file_name, '/') );
        $objectPathForSignature = '/'. $this->space .'/'. $file_name;
        $stringToSign = implode("\n", $pieces = array('GET', null, null, $expiry, $objectPathForSignature));
        $url = 'https://' . $this->space . '.'.$this->region.'.'.$this->host.'/' . $file_name;
        $blocksize = 64;
        if (strlen($secret_key) > $blocksize) $secret_key = pack('H*', sha1($secret_key));
        $secret_key = str_pad($secret_key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack( 'H*', sha1(($secret_key ^ $opad) . pack( 'H*', sha1(($secret_key ^ $ipad) . $stringToSign))));
        $signature = base64_encode($hmac);
        $queries = http_build_query($pieces = array(
                  'AWSAccessKeyId' => $this->access_key,
                  'Expires' => $expiry,
                  'Signature' => $signature,
                 ));
       $url .= "?".$queries;
       return $this->ObjReturn($url);
    }



    function ObjReturn($return) {
      $return = @json_decode(@json_encode($return), true);
      $return = $this->AWSTime($return);
      return $return;
    }

    function AWSTime($obj) {
      $time_keys = ["LastModified", "CreationDate", "Expires", "last-modified", "date", "Expiration"];
      if(is_array($obj)) {
        foreach ($obj as $key => $value) {
          if(is_array($obj[$key])) {
            $obj[$key] = $this->AWSTime($obj[$key]);
          }
          else {
            foreach ($time_keys as $time_key) {
              if(array_key_exists($time_key, $obj) && !empty($obj[$time_key]) && !is_numeric($obj[$time_key])) {
                  $obj[$time_key] = strtotime($obj[$time_key]);
              }
            }
          }
        }
      }
      return $obj;
    }

    private function any_key_exists($keys, $arr) {
      foreach ($keys as $key) {
        if(array_key_exists($key, $arr)) {
          return true;
        }
      }
      return false;
    }


    function HandleAWSException($e) {
      if(get_class($e) == "Aws\S3\Exception\S3Exception") {
        $error["error"] = [
          "message" => $e->getAwsErrorMessage(),
          "code" => $e->getAwsErrorCode(),
          "type" => $e->getAwsErrorType(),
          "http_code" => $e->getStatusCode(),
        ];
       }
      else {
        throw $e;
      }
      throw new SpacesAPIException(@json_encode($error));
    }

    function GetError($e) {
      $error = @json_decode($e->getMessage(), true);
      return $error["error"];
    }
}

class SpacesAPIException extends \Exception {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
