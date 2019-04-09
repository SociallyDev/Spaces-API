# Spaces-API
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FSociallyDev%2FSpaces-API.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2FSociallyDev%2FSpaces-API?ref=badge_shield)

An API wrapper for DigitalOcean's Spaces object storage designed for easy use. 


### Installation
* **Using Composer**:
```sh
composer require sociallydev/spaces-api:dev-master
```

### Connecting
```php
//Either:
require_once("spaces.php");
//OR COMPOSER:
require_once("vendor/autoload.php"); //Install first by executing: composer require SociallyDev/Spaces-API in your project's directory.

$key = "EXAMPLE_KEY";
$secret = "EXAMPLE_SECRET";

$space_name = "my-space";
$region = "nyc3";

$space = new SpacesConnect($key, $secret, $space_name, $region);
```

All available options: 
###### SpacesConnect(REQUIRED KEY, REQUIRED SECRET, OPTIONAL SPACE's NAME, OPTIONAL REGION, OPTIONAL HOST);



&nbsp;


### Uploading/Downloading Files
```php
// Don't start any path with a forward slash, or it will give "SignatureDoesNotMatch" exception
$path_to_file = "image.png";

$space->UploadFile($path_to_file, "public");



$download_file = "image.png";
$save_as = "folder/downloaded-image.png";

$space->DownloadFile($download_file, $save_as);
```
All available options: 
###### UploadFile(REQUIRED PATH TO FILE, OPTIONAL PRIVACY (public|private) OPTIONAL NAME TO SAVE FILE AS);
###### DownloadFile(REQUIRED FILE TO DOWNLOAD, REQUIRED LOCATION TO SAVE IN);




&nbsp;

### Changing Privacy Settings
```php
$file = "image.png";

$space->MakePublic($file);
$space->MakePrivate($file);

```
All available options: 
###### MakePublic(REQUIRED PATH TO FILE);
###### MakePrivate(REQUIRED PATH TO FILE);




&nbsp;

### Creating Temporary Links
```php
$file = "image.png";
$valid_for = "1 day";

$link = $space->CreateTemporaryURL($file, $valid_for);
```
All available options: 
###### CreateTemporaryURL(REQUIRED FILE NAME, OPTIONAL TIME LINK IS VALID FOR);


&nbsp;
&nbsp;

### Other File APIs
```php
//List all files and folders
$files = $space->ListObjects();


//Check if a file/folder by that name already exists. True/False.
$space->DoesObjectExist($file_name);


//Pull information about a single object.
$file_info = $space->GetObject($file_name);


//Delete a file/folder.
$space->DeleteObject($file_name);


//Upload a complete directory instead of a single file.
$space->UploadDirectory($path_to_directory, $key_prefix);


//Pull Access Control List information.
$acl = $space-ListObjectACL($file_name);


//Update Access Control List information.
$space->PutObjectACL($file_name, $acl_info_array);

```





&nbsp;
&nbsp;
&nbsp;
&nbsp;
&nbsp;


### Creating Spaces
```php
$new_space = "my-new-space";

$space->CreateSpace($new_space);
```
All available options: 
###### CreateSpace(REQUIRED SPACE NAME, OPTIONAL REGION FOR SPACE);


&nbsp;

### Switching Spaces
```php
$new_space = "my-new-space";

$space->SetSpace($new_space);
```
All available options: 
###### SetSpace(REQUIRED SPACE NAME, OPTIONAL REGION FOR SPACE, OPTIONAL HOST);


&nbsp;
&nbsp;

### Other Spaces APIs
```php
//List all Spaces available in account.
$spaces = $space->ListSpaces();


//Delete a Space.
$space->DestroyThisSpace();


//Download whole Space to a folder.
$space->DownloadSpaceToDirectory($directory_to_download_to);


//Get the name of the current Space.
$space_name = $space->GetSpaceName();


//Pull the CORS policy of the Space.
$cors = $space->ListCORS();


//Update the CORS policy of the Space.
$space->PutCORS($new_policy);


//Pull the Access Control List information of the Space.
$acl = $space->ListSpaceACL();


//Update the Access Control List information of the Space.
$space->PutSpaceACL($new_acl);
```




### Handling Errors

```php
try {
   $space->CreateSpace("dev");
} catch (\SpacesAPIException $e) {
  $error = $e->GetError();

   //Error management code.
   echo "<pre>";
   print_r($error);
   /*
   EG:
   Array (
    [message] => Bucket already exists
    [code] => BucketAlreadyExists
    [type] => client
    [http_code] => 409
   )
   */
}
```


## License
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FSociallyDev%2FSpaces-API.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FSociallyDev%2FSpaces-API?ref=badge_large)
