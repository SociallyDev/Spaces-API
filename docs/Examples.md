# Examples

## Connecting to Digital Ocean and selecting a space

```php
use SpacesAPI\Spaces;

$spaces = new Spaces('api-key', 'api-secret');
$space = $spaces->space('space-name');
```
[API docs for \SpacesAPI\Spaces](Spaces.md)

## Creating a new space
```php
$spaces = new Spaces('api-key', 'api-secret');
$space = $spaces->create('new-space-name');
```
[API docs for creating a space](Spaces.md#spacescreate)

## Listing files

```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
$files = $space->listFiles();

foreach ($files['files'] as $file) {
    echo "{$file->filename}\n";
}
```
[API docs for listing files](Space.md#spacelistfiles)

## Uploading a file

```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
$file = $space->uploadFile('./localfile.txt');
```
[API docs for uploading files](Space.md#spaceuploadfile)

## Uploading text to a file
```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
$file = $space->uploadText('Lorem ipsum', 'remote-filename.txt');
```

[API docs for uploading text](Space.md#spaceuploadtext)

## Downloading a file
```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
$space->file('filename.txt')->download('./localfile.txt');
```

[API docs for downloading a file](File.md#filedownload)

## Get the contents of a file

```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
echo $space->file('filename.txt')->getContents();
```

[API docs for getting the contents of a file](File.md#filegetcontents)

## Deleting a file
```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
$space->file('filename.txt')->delete();
```

[API docs for deleting a file](File.md#filedelete)

## Get a public URL

```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
$space->file('filename.txt')->getURL();
```

[API docs for getting the public URL](File.md#filegeturl)

## Get a signed URL
#### a time limited link to provide access to a private file

```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
$space->file('filename.txt')->getSignedURL("1 day");
```

[API docs for getting a signed URL](File.md#filegetsignedurl)

## Make a file publicly accessible
```php
$space = new Spaces('api-key', 'api-secret')->space('my-space-name');
$space->file('filename.txt')->makePublic();
```

[API docs for setting file privacy](File.md#spacemakeprivate)
