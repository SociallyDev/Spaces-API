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
        $this->setSpace($spaceName);
        $success = $this->client->createBucket(array('Bucket' => $spaceName));
        $this->client->waitUntil('BucketExists', array('Bucket' => $spaceName));
        $this->setSpace($current_space);
        return $success;
    }

    function listSpaces() {
        $current_space = $this->space;
        $this->setSpace(NULL);
        $spaceList = $this->client->listBuckets();
        $this->setSpace($current_space);
        return $spaceList;
    }

    function ChangeSpace($spaceName, $region = "", $host = "") {
      setSpace($spaceName, $region, $host);
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
    }

    function getSpaceName() {
        return $this->space;
    }

    function destroyThisSpace() {
        $this->setSpace(NULL);
        $this->client->deleteBucket(array('Bucket' => $this->space));
        $this->client->waitUntil('BucketNotExists', array('Bucket' => $this->space));
    }





    function listObjects() {
        $objects = $this->client->getIterator('ListObjects', array(
            'Bucket' => $this->space
        ));
        $objectArray = array();
        foreach ($objects as $object) {
          $objectArray[] = $object;
        }
        return $objectArray;
    }

    function doesObjectExist($objectName) {
        return $this->client->doesObjectExist($this->space, $objectName);
    }

    function getObject($file_name = "") {
      $result = $this->client->getObject([
        'Bucket' => $this->space,
        'Key' => $file_name,
       ]);
       return $result;
    }

    function deleteObject($file_path = "") {
        return $this->client->deleteObject([
        'Bucket' => $this->space,
        'Key' => $file_path,
       ]);
    }

    function uploadFile($pathToFile, $fileName = "") {
        if(empty($filename)) {
          $fileName = $pathToFile;
        }
        $result = $this->client->putObject(array(
            'Bucket'  => $this->space,
            'Key'     => $fileName,
            'Body'    => fopen($pathToFile, 'r+')
        ));

        $this->client->waitUntil('ObjectExists', array(
            'Bucket' => $this->space,
            'Key'    => $fileName
        ));

        return $result;
    }

    function downloadFile($fileName, $destinationPath = "") {
        $result = $this->client->getObject(array(
            'Bucket' => $this->space,
            'Key'    => $fileName,
            'SaveAs' => $destinationPath
        ));
    }

    function uploadDirectory($directory, $keyPrefix = "") {
        $this->client->uploadDirectory($directory, $this->space, $keyPrefix);
    }

    function downloadSpaceToDirectory($pathToDirectory) {
        $this->client->downloadBucket($pathToDirectory, $this->space);
    }





    function listCORS() {
       $cors = $this->client->getBucketCors([
         'Bucket' => $this->space,
        ]);
        return $cors;
    }

    function putCORS($cors_rules = "") {
      if(empty($cors_rules)) {
        $cors_rules = [
         'AllowedMethods' => ['GET'],
         'AllowedOrigins' => ['*'],
         'ExposeHeaders' => ['Access-Control-Allow-Origin'],
         ];
        }
       $result = $this->client->putBucketCors([
         'Bucket' => $this->space,
         'CORSConfiguration' => ['CORSRules' => [$cors_rules]]
       ]);
       return $result;
    }

    function listBucketACL() {
       $acl = $this->client->getBucketAcl([
         'Bucket' => $this->space,
        ]);
        return $acl;
    }


    function PutBucketACL($params) {
       $acl = $s3Client->putBucketAcl($params);
        return $acl;
    }

    function listObjectACL($file) {
        $result = $client->getObjectAcl([
           'Bucket' => $this->space,
           'Key' => $file,
        ]);
        return $result;
    }

    function PutObjectACL($file, $acl) {
      $acl = array_merge (array("Bucket" => $this->bucket, "Key" => $file), $acl);
      $result = $client->putObjectAcl($acl);
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
       return $url;
    }
}
