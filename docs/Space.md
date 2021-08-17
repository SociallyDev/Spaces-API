# SpacesAPI\Space

Represents a space once connected/created

You wouldn't normally instantiate this class directly,
Rather obtain an instance from `\SpacesAPI\Spaces::space()` or `\SpacesAPI\Spaces::create()`


## Methods

| Name | Description |
|------|-------------|
|[__construct](#space__construct)|Load a space|
|[addCORSOrigin](#spaceaddcorsorigin)|Add an origin to the CORS settings on this space|
|[deleteDirectory](#spacedeletedirectory)|Delete an entire directory, including its contents|
|[destroy](#spacedestroy)|Destroy/Delete this space|
|[downloadDirectory](#spacedownloaddirectory)|Recursively download an entire directory.|
|[file](#spacefile)|Get an instance of \SpacesAPI\File for a given filename|
|[getCORS](#spacegetcors)|Get the CORS configuration for the space|
|[getName](#spacegetname)|Get the name of this space|
|[getS3Client](#spacegets3client)|Get the current AWS S3 client instance (internal use)|
|[isPublic](#spaceispublic)|Is file listing enabled?|
|[listFiles](#spacelistfiles)|List all files in the space (recursively)|
|[makePrivate](#spacemakeprivate)|Disable file listing|
|[makePublic](#spacemakepublic)|Enable file listing|
|[removeCORSOrigin](#spaceremovecorsorigin)|Remove an origin from the CORS settings on this space|
|[removeAllCORSOrigins](#spacedeleteallcorsorigins)|Delete all CORS rules|
|[uploadDirectory](#spaceuploaddirectory)|Recursively upload an entire directory|
|[uploadFile](#spaceuploadfile)|Upload a file|
|[uploadText](#spaceuploadtext)|Upload a string of text to file|




### Space::__construct

**Description**

```php
public __construct (\Aws\S3\S3Client $s3, string $name, bool $validate = true)
```

Load a space

You wouldn't normally call this directly,
rather obtain an instance from `\SpacesAPI\Spaces::space()` or `\SpacesAPI\Spaces::create()`

**Parameters**

* `(\Aws\S3\S3Client) $s3`
: An authenticated S3Client instance
* `(string) $name`
: Space name
* `(bool) $validate`
: Check that the space exists. Default `true`

**Return Values**

`void`


**Throws Exceptions**


`\SpacesAPI\Exceptions\SpaceDoesntExistException` : If validation is `true` and the space doesn't exist


<hr />


### Space::addCORSOrigin

**Description**

```php
public addCORSOrigin (string $origin, array $methods, int $maxAge = 0, array $headers => [])
```

Add an origin to the CORS settings on this space



**Parameters**

* `(string) $origin`
: eg `http://example.com`
* `(array) $methods`
: Array items must be one of `GET`, `PUT`, `DELETE`, `POST` and `HEAD`
* `(int) $maxAge`
: Access Control Max Age. Default `0`
* `(array) $headers`
: Allowed Headers. Default `[]`

**Return Values**

`void`


<hr />


### Space::deleteDirectory

**Description**

```php
public deleteDirectory (string $path)
```

Delete an entire directory, including its contents



**Parameters**

* `(string) $path`
: The directory to delete

**Return Values**

`void`


<hr />


### Space::destroy

**Description**

```php
public destroy (void)
```

Destroy/Delete this space, along with all files.



**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Space::downloadDirectory

**Description**

```php
public downloadDirectory (string $local, string|null $remote = null)
```

Recursively download an entire directory.



**Parameters**

* `(string) $local`
: The local directory to save the directories/files in
* `(string|null) $remote`
: The remote directory to download. `null` to download the entire space. Default `null`

**Return Values**

`void`


<hr />


### Space::file

**Description**

```php
public file (string $filename)
```

Get an instance of \SpacesAPI\File for a given filename



**Parameters**

* `(string) $filename`

**Return Values**

`\SpacesAPI\File`




**Throws Exceptions**


`\SpacesAPI\Exceptions\FileDoesntExistException`
> Thrown if the file doesn't exist

<hr />


### Space::getCORS

**Description**

```php
public getCORS (void)
```

Get the CORS configuration for the space



**Parameters**

`This function has no parameters.`

**Return Values**

`array|null`

> An array of CORS rules or `null` if no rules exist


<hr />


### Space::getName

**Description**

```php
public getName (void)
```

Get the name of this space



**Parameters**

`This function has no parameters.`

**Return Values**

`string`




<hr />


### Space::getS3Client

**Description**

```php
public getS3Client (void)
```

Get the current AWS S3 client instance

For internal library use. It is unlikely you will need to access this object, but can do so to gain access to the underlying S3Client for andvanced usage.

**Parameters**

`This function has no parameters.`

**Return Values**

`\Aws\S3\S3Client`




<hr />


### Space::isPublic

**Description**

```php
public isPublic (void)
```

Is file listing enabled?



**Parameters**

`This function has no parameters.`

**Return Values**

`bool`




<hr />


### Space::listFiles

**Description**

```php
public listFiles (string $directory = "")
```

List all files in the space (recursively)



**Parameters**

* `(string) $directory`
: The directory to list files in. Empty string for root directory

**Return Values**

`array`

> An array of `\SpacesAPI\File` instances indexed by the file name

<hr />


### Space::makePrivate

**Description**

```php
public makePrivate (void)
```

Disable file listing



**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Space::makePublic

**Description**

```php
public makePublic (void)
```

Enable file listing



**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Space::removeCORSOrigin

**Description**

```php
public removeCORSOrigin (string $origin)
```

Remove an origin from the CORS settings on this space



**Parameters**

* `(string) $origin`
: eg `http://example.com`

**Return Values**

`void`


<hr />

### Space::removeAllCORSOrigins

**Description**

```php
public deleteAllCORSOrigins (void)
```

Delete all CORS rules

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Space::uploadDirectory

**Description**

```php
public uploadDirectory (string $local, string|null $remote = null)
```

Recursively upload an entire directory



**Parameters**

* `(string) $local`
: The local directory to upload
* `(string|null) $remote`
: The remote directory to place the files in. `null` to place in the root. Default `null`

**Return Values**

`void`


<hr />


### Space::uploadFile

**Description**

```php
public uploadFile (string $filepath, string|null $filename = null)
```

Upload a file



**Parameters**

* `(string) $filepath`
: The path to the file, including the filename. Relative and absolute paths are accepted.
* `(string|null) $filename`
: The remote filename. If `null`, the local filename will be used. Default `null`

**Return Values**

`\SpacesAPI\File`




<hr />


### Space::uploadText

**Description**

```php
public uploadText (string $text, string $filename, array $params => [])
```

Upload a string of text to file



**Parameters**

* `(string) $text`
: The text to upload
* `(string) $filename`
: The filepath/name to save to
* `(array) $params`
: Any extra parameters. [See here](https://docs.aws.amazon.com/AWSJavaScriptSDK/latest/AWS/S3.html#upload-property)

**Return Values**

`\SpacesAPI\File`




<hr />

