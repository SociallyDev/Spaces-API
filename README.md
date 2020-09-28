![Spaces-API](https://i.imgur.com/j70JvUc.png "Devang Srivastava's Spaces-API")

Makes using DigitalOcean's Spaces object storage super easy.
&nbsp;

* Makes everything super simple.
* Automatically handles multipart & stream uploads for large files.
* Uses Spaces terminology for objects instead of S3.

&nbsp;
# Example
Create a Space & upload, it's as easy as that.

```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Upload some text.
$my_space->upload("Super cool content", "example.txt");

//Uploaded!
```

### tl;dr

- Files

  - [Uploading a file](#upload-a-stored-file)
  - [Uploading text](#upload-text-directly)
  - [Uploading an entire folder](#upload-a-local-folder)
  - [Downloading a file](#download-a-file)
  - [Downloading your entire Space](#download-your-space-to-a-local-folder)
  - [Copy a Space file](#copy-a-file-inside-your-space)
  - [List all files](#list-all-files-inside-your-space)
  - [Check if file exists](#check-whether-a-file-exists)
  - [Retrieve file info](#get-info-on-a-file)
  - [Create signed temporary sharing URL](#create-a-signed-url-used-for-sharing-private-files)
  - [Retrieve public file URL](#create-an-unsigned-url-used-for-sharing-public-files)
  - [Change a file's privacy](#change-a-files-privacy-public--private-acl)
  - [Delete a file](#delete-a-file)
  - [Delete an entire folder](#delete-an-entire-folder)


- Spaces

  - [Create a new Space](#create-a-new-space)
  - [Use an existing Space](#use-an-existing-space)
  - [List all Spaces](#list-all-spaces)
  - [Change your Space's privacy](#change-your-spaces-privacy-acl)
  - [List your Space's CORS rules](#list-your-spaces-cors-rules)
  - [Change your Space's CORS rules](#set-your-spaces-cors-rules)
  - [Destroy your Space](#destroy-your-space)


&nbsp;
# Installing Spaces-API
There are two ways to install Spaces-API. You can either download the project & put it directly in your code's folder, or you can use [Composer](https://getcomposer.org).

&nbsp;
### a) The Manual Way

1) [Download Spaces-API](https://github.com/SociallyDev/Spaces-API/archive/master.zip) & place it in your project.
2) Load it from your code:

```php
require_once("spaces.php");
```
&nbsp;
### b) The Composer Method

1) Make sure you have [Composer](https://getcomposer.org).
2) Install Spaces-API:
```
composer require sociallydev/spaces-api:dev-master
```
3) Make sure your code has the Composer autoloader:
```php
require_once("vendor/autoload.php");
```
&nbsp;

## Setup
You'll need a DigitalOcean account & API keys to use Spaces-API. You should be able to generate a pair of keys from the [DigitalOcean Dashboard](https://cloud.digitalocean.com/account/api/tokens). In the API page, there should be a section with the title "Spaces access keys". Click "Generate New Key" & follow the steps. That'll give you an access key & a secret key.

We'll be using these keys to initiate a Spaces instance:

```php
$spaces = Spaces("ACCESS KEY", "SECRET KEY");
```
&nbsp;

## Usage
Once you have a Spaces instance, you can do pretty much anything the Spaces API allows.

Here is an example of downloading all your files to a local directory:

```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Download entire Space to a directory.
$my_space->downloadToDirectory("local_backup");
```

&nbsp;
&nbsp;
&nbsp;
&nbsp;


![Spaces-API](https://i.imgur.com/pH7QqtP.png "Spaces-API API Reference")

&nbsp;

# Top-Level Spaces Functions
These are all the functions a Spaces instance can perform.


&nbsp;
## Create a new Space
```php
$spaces = Spaces("ACCESS KEY", "SECRET KEY");

//Creates a new Space.
$spaces->create("my-new-space", "nyc3", "private");
```

* The third (Space privacy) argument is optional. Defaults to private.
* Returns a new single Space instance. Same as `$spaces->space("my-new-space")`.


&nbsp;
## Use an existing Space
```php
$spaces = Spaces("ACCESS KEY", "SECRET KEY");

//Get an existing Space.
$my_space = $spaces->space("my-space", "nyc3");
```

&nbsp;
## List all Spaces
```php
$spaces = Spaces("ACCESS KEY", "SECRET KEY");

//Returns an array of all available Spaces.
$spaces->list();
```

&nbsp;

***
&nbsp;

# Single Space Functions
These are all the functions a Space instance (From `$spaces->space("my-new-space")`) can perform!

&nbsp;
## Upload a stored file
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Upload a local stored file.
$my_space->uploadFile("path/to/my/file.txt", "path/on/space.txt", "private");
```
* The second (Save as) argument is optional. Spaces-API attempts to use the original file path as the path on your Space if no save as path is provided.
* The third argument (File privacy) is optional. It defaults to private.
* Returns available info on the file.

&nbsp;
## Upload text directly
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Upload a local stored file.
$my_space->upload("my content", "path/on/space.txt", "private");
```
* The first argument (Content) can be a string, but it can also be a StreamInterface or PHP stream resource.
* The third argument (File privacy) is optional. It defaults to private.
* Returns available info on the file.

&nbsp;
## Upload a local folder
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Uploads an entire local directory.
$my_space->uploadDirectory("my-local-folder");
```
* You can provide a second argument which can be a folder inside your Space where to upload all the files inside the local folder.

&nbsp;
## Download a file
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Returns the file content as result.
$content = $my_space->downloadFile("my-file.txt");

//Saves the file content to the local path, and returns file info.
$info = $my_space->downloadFile("my-file.txt", "save-path/file.txt");
```

&nbsp;
## Download your Space to a local folder
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Uploads an entire local directory.
$my_space->downloadToDirectory("my-local-folder");
```
* You can provide a second argument which can be a folder on your Space. Spaces-API will only download the files inside this folder.

&nbsp;
## Copy a file inside your Space
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Copies the file to the same Space.
$my_space->copyFile("my-file.txt", "my-new-file.txt");

//Copies the file to another Space.
$my_space->copyFile("my-file.txt", "my-new-file.txt", "my-other-space");
```
* DigitalOcean only allows copying across Spaces in the same region.
* You can supply a fourth argument to set the new file's privacy (public/private).
* Returns info on the new file.

&nbsp;
## List all files inside your Space
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Lists all files inside your Space.
$my_space->listFiles();

//Lists all files in a folder inside your Space.
$my_space->listFiles("my-folder");
```
* This function automatically iterates till it gets all files but you can set the second argument to false if you want to handle pagination yourself.
* If you set the second argument to false, you can set the third argument to a ContinuationToken.
* Returns an array of files but if the second argument is set to false, the original object is returned.

&nbsp;
## Check whether a file exists
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Returns true if this file exists, otherwise false.
$my_space->fileExists("my-file.txt");
```

&nbsp;
## Get info on a file
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Returns all available data on a file.
$my_space->fileInfo("my-file.txt");
```

&nbsp;
## Create a signed URL (Used for sharing private files)
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Returns a URL that'll work for 15 minutes for this file.
$my_space->signedURL("my-file.txt", "15 minutes");
```

&nbsp;
## Create an unsigned URL (Used for sharing public files)
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Returns a URL that'll work as long as the file is public.
$my_space->url("my-file.txt");
```

&nbsp;
## Change a file's privacy (Public & Private ACL)
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Make a file public.
$my_space->filePrivacy("my-file.txt", "public");

//Make a file private.
$my_space->filePrivacy("my-file.txt", "private");
```

&nbsp;
## Delete a file
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Deletes a single file.
$my_space->deleteFile("my-file.txt");
```

&nbsp;
## Delete an entire folder
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Deletes all content of a folder (Or any file paths that match the provided check string).
$my_space->deleteFolder("my-folder");
```

&nbsp;
## Change your Space's privacy (ACL)
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Makes your Space public. All your Space's files will be displayed to anyone.
$my_space->privacy("public");

//Makes your Space private.
$my_space->privacy("private");
```

&nbsp;
## List your Space's CORS rules
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Returns an array of your Space's CORS rules.
$my_space->getCORS();
```
* This will throw an error if no CORS rules exist.

&nbsp;
## Set your Space's CORS rules
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//This allows all sources to use your file.
$my_space->setCORS([["origins" => ["*"], "methods" => ["GET", "HEAD", "OPTIONS"]]]);
```

&nbsp;
## Destroy your Space
Also deletes all files inside your Space
```php
$my_space = Spaces("ACCESS KEY", "SECRET KEY")->space("my_space", "nyc3");

//Destroys your Space & deletes all its files.
$my_space->destroy();
```
