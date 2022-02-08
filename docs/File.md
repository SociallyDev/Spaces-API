# SpacesAPI\File

Represents a single file

You wouldn't normally instantiate this class directly,
Rather obtain an instance from `\SpacesAPI\Space::list()`, `\SpacesAPI\Spaces::file()`, `\SpacesAPI\Spaces::uploadText()` or `\SpacesAPI\Spaces::uploadFile()`


## Properties
| name | Type | Description |
| --- | --- | --- |
| `expiration` | `string` | |
| `e_tag` | `string` | |
| `last_modified` | `int` | Last modified date as unix timestamp |
| `content_type` | `string` | THe mime type of the file |
| `content_length` | `int` | The size of the file in bytes |


## Methods

| Name | Description |
|------|-------------|
|[__construct](#file__construct)||
|[copy](#filecopy)|Copy the file on the space|
|[delete](#filedelete)|Permanently delete this file|
|[download](#filedownload)|Download the file to a local location|
|[getContents](#filegetcontents)|Get the file contents as a string|
|[getSignedURL](#filegetsignedurl)|Get a signed URL, which will work for private files|
|[getURL](#filegeturl)|Get the public URL. This URL will not work if the file is private|
|[isPublic](#fileispublic)|Is this file publicly accessible|
|[makePrivate](#filemakeprivate)|Make file non-publicly accessible|
|[makePublic](#filemakepublic)|Make file publicly accessible|
|[move](#filemove)|Move and/or rename file|




### File::__construct

**Description**

```php
 __construct (\SpacesAPI\Space $space, string $filename, array $info = [], bool $validate = true)
```

**Parameters**

* `(\SpacesAPI\Space) $space` : An instance of `\SpacesAPI\Space`
* `(string) $filename` : The filename of a file
* `(array) $info` : Any information already known about the file (eg content_length, content_type, etc). Default `[]`
* `(bool) $validate` : Check that the file exists. Default `true`

**Return Values**

`void`

**Throws Exceptions**

`\SpacesAPI\Exceptions\FileDoesntExistException` : If validation is `true` and the file doesn't exist


<hr />


### File::copy

**Description**

```php
public copy (string $newFilename, bool $public = false)
```

Copy the file on the space



**Parameters**

* `(string) $newFilename`
* `(bool) $public`

**Return Values**

`\SpacesAPI\File` : An instance for the new file




<hr />


### File::delete

**Description**

```php
public delete (void)
```

Permanently delete this file



**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### File::download

**Description**

```php
public download (string $saveAs)
```

Download the file to a local location



**Parameters**

* `(string) $saveAs` Then filepath including the filename. This can be a relative or absolute path.

**Return Values**

`void`




<hr />


### File::getContents

**Description**

```php
public getContents (void)
```

Get the file contents as a string



**Parameters**

`This function has no parameters.`

**Return Values**

`string`




<hr />


### File::getSignedURL

**Description**

```php
public getSignedURL (string|\DateTime|int $validFor)
```

Get a signed URL, which will work for private files



**Parameters**

* `(string|\DateTime|int) $validFor`
: Can be any string recognised by strtotime(), an instance of `\DateTime` or a unix timestamp

**Return Values**

`string`




<hr />


### File::getURL

**Description**

```php
public getURL (void)
```

Get the public URL. This URL will not work if the file is private



**Parameters**

`This function has no parameters.`

**Return Values**

`string`




<hr />


### File::isPublic

**Description**

```php
public isPublic (void)
```

Is this file publicly accessible?



**Parameters**

`This function has no parameters.`

**Return Values**

`bool`




<hr />


### File::makePrivate

**Description**

```php
public makePrivate (void)
```

Make file non-publicly accessible



**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### File::makePublic

**Description**

```php
public makePublic (void)
```

Make file publicly accessible



**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### File::move

**Description**

```php
public move (string $newFilename)
```

Move or rename a file
The `File` instance on which you call `move` will become invalid and calling methods on it will result in a `FileDoesntExistException`


**Parameters**

* `(string) $newFilename`

**Return Values**

`\SpacesAPI\File` : An instance for the new file


<hr />
