# Spaces-API
An API wrapper for DigitalOcean's Spaces object storage designed for easy use. 

&nbsp;

### Connecting
```php
require_once("spaces.php");

$key = "EXAMPLE_KEY";
$secret = "EXAMPLE_SECRET";

$space = "my-space";

$space = new SpacesConnect($key, $secret, $space);
```

All available options: 
###### SpacesConnect( REQUIRED KEY, REQUIRED SECRET, OPTIONAL SPACE's NAME, OPTIONAL REGION, OPTION HOST DOMAIN );



### Uploading/Downloading Files
```php
$path_to_file = "folder/my-image.png";
$optional_file_name = "image.png";

$space->uploadFile($path_to_file, $optional_file_name);
```
All available options: 
###### uploadFile( REQUIRED PATH TO FILE, OPTIONAL NAME TO SAVE FILE AS);



### Creating Temporary Links
```php
$file = "image.png";
$valid_for = "1 day";

$link = $space->CreateTemporaryURL($file, $valid_for);
```
All available options: 
###### CreateTemporaryURL( REQUIRED FILE NAME, OPTIONAL TIME LINK IS VALID FOR);



### Creating Spaces
```php
$new_space = "my-new-space";

$space->createSpace($new_space);
```
All available options: 
###### createSpace( REQUIRED SPACE NAME, OPTIONAL REGION FOR SPACE);




