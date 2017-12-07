# Spaces-API
An API wrapper for DigitalOcean's Spaces object storage designed for easy use. 

### Connecting
```php
require_once("spaces.php");

$key = "EXAMPLE_KEY";
$secret = "EXAMPLE_SECRET";

$space_name_optional = "my-space";

$space = new SpacesConnect($key, $secret, $space_name_optional);
```

All available options: 
###### SpacesConnect(REQUIRED KEY, REQUIRED SECRET, OPTIONAL SPACE's NAME, OPTIONAL REGION, OPTION HOST DOMAIN);



&nbsp;


### Uploading/Downloading Files
```php
$path_to_file = "folder/image.png";

$space->uploadFile($path_to_file);



$download_file = "image.png";
$save_as = "folder/downloaded-image.png";

$space->downloadFile($download_file, $save_as);
```
All available options: 
###### uploadFile(REQUIRED PATH TO FILE, OPTIONAL NAME TO SAVE FILE AS);
###### downloadFile(REQUIRED FILE TO DOWNLOAD, REQUIRED LOCATION TO SAVE FILE);




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
$files = $space->listObjects();


//Check if a file/folder by that name already exists. True/False.
$space->doesObjectExist($file_name);


//Pull information about a single object.
$file_info = $space->getObject($file_name);


//Delete a file/folder.
$space->deleteObject($file_name);


//Upload a complete directory instead of a single file.
$space->uploadDirectory($path_to_directory, $key_prefix);


//Pull Access Control List information.
$acl = $space-listObjectACL($file_name);


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

$space->createSpace($new_space);
```
All available options: 
###### createSpace(REQUIRED SPACE NAME, OPTIONAL REGION FOR SPACE);


&nbsp;

### Switching Spaces
```php
$new_space = "my-new-space";

$space->setSpace($new_space);
```
All available options: 
###### setSpace(REQUIRED SPACE NAME, OPTIONAL REGION FOR SPACE, OPTIONAL HOST);


&nbsp;
&nbsp;

### Other Spaces APIs
```php
//List all Spaces available in account.
$spaces = $space->listSpaces();


//Delete a Space.
$space->destroyThisSpace();


//Download whole Space to a folder.
$space->downloadSpaceToDirectory($directory_to_download_to);


//Get the name of the current Space.
$space_name = $space->getSpaceName();


//Pull the CORS policy of the Space.
$cors = $space->listCORS();


//Update the CORS policy of the Space.
$space->putCORS($new_policy);


//Pull the Access Control List information of the Space.
$acl = $space->listSpaceACL();


//Update the Access Control List information of the Space.
$space->PutSpaceACL($new_acl);
```

