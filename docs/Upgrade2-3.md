# Upgrade guide for v2 to v3

A lot has changed in this version release, so please read carefully to ensure you make all necessary changes.

## Namespace
Spaces-API is now namespaced, so you will need to add `use SpacesAPI\Spaces;` to the top of your files.

## Method changes
### `Spaces::__construct()`
Region is now passed into the Spaces constructor instead of the Space.

Old signature:
```
Spaces::__construct($accessKey, $secretKey, $host = "digitaloceanspaces.com")
```

New signature
```
Spaces::__construct(string $accessKey, string $secretKey, string $region = "ams3", string $host = "digitaloceanspaces.com")
```

***

### `Spaces::listSpaces()`
Method name has changed to `Spaces::list()`

***

### `Spaces::space()`
Signature has changed as the region is now passed into the `Spaces` constructor

Old signature:

```
Spaces::space($name, $region = "ams3")
```

New signature

```
Spaces::space(string $name)
```

***

### `Space::__contruct()`
Signature has changed as S3 credentials are no longer passed in, nor the region or host.

Old signature:

```
Space::__construct($name, $region, $accessKey, $secretKey, $host)
```

New signature

```
Space::__construct(S3Client $s3, string $name)
```

***

### `Space::create()`
This method has moved to the `Spaces` class and the signature has changed

Old signature:

```
Space::create($privacy = "private")
```

New signature

```
Spaces::create(string $name, bool $public = false)
```

***

### `Space::downloadToDirectory()`
Method name has changed. Parameter names have changed, but function the same way.

Old signature:

```
Space::downloadToDirectory($directory, $filesStartingAs = "")
```

New signature

```
Spaces::downloadDirectory(string $local, ?string $remote = null)
```

***

### `Space::upload()`
Method name and signature has changed

Old signature:

```
Space::upload($text, $saveAs, $privacy = "private", $params = array())
```

New signature

```
Spaces::uploadText(string $text, string $filename, array $params = [])
```

***

### `Space::uploadFile()`
`$privacy` parameter removed from signature. Parameter names have changed, but function the same way.

Old signature:

```
Space::uploadFile($filePath, $saveAs = "", $privacy = "private")
```

New signature

```
Spaces::uploadFile(string $filepath, ?string $filename = null)
```

***

### `Spaces::downloadFile()`
Method has moved to `File`, changed name and changed signature

Old signature:

```
Space::downloadFile($file, $saveTo = false)
```

New signature

```
File::download(string $saveAs)
```

***

### `Space::copyFile()`
Method has moved to `File`, changed name and changed signature

Old signature:

```
Space::copyFile($filePath, $saveAs, $toSpace = "", $privacy = "private")
```

New signature

```
File::copy(string $newFilename, bool $public = false)
```

***

### `Space::listFiles()`
Method signature has changed. This shouldn't have much impact as almost no-one should be using the second/third argument.

Old signature:

```
Space::listFiles($ofFolder = "", $autoIterate = true, $continueAfter = null)
```

New signature

```
Space::listFiles(string $directory = "", ?string $continuationToken = null)
```

***

### `Space::fileExists()`
Method removed.

If you need to check for file existence, instantiate a `File` object from the space, and catch the `FileDoesntExistException`

```php
try {
    $space->file("filename.txt");
} catch (FileDoesntExistException $e) {
    // Uh oh, the file doesn't exist
}
```

***

### `Space::fileInfo()`
Method removed

File information is now stored in properties on the `File` object

```php
$file = $space->file('filename.txt');
$file->content_type;
$file->content_length;
$file->expiration;
$file->e_tag;
$file->last_modified;
```

***

### `Space::url()`
Method moved to `File` and signature has changed

Old signature:

```
Space::url($path)
```

New signature

```
File::getURL()
```

***

### `Space::signedURL()`
Method moved to `File` and signature has changes

Old signature:

```
Space::signedURL($path, $validFor = "15 minutes")
```

New signature

```
File::getSignedURL($validFor = "15 minutes")
```

***

### `Space::deleteFolder()`
Method name has changed. Parameter name has changed, but meaning remains the same.

Old signature:

```
Space::deleteFolder($prefixOrPath)
```

New signature

```
File::deleteDirectory(string $path)
```

***

### `Space::deleteFile()`
Method has moved to `File` and signature has changed.

Old signature:

```
Space::deleteFile($path)
```

New signature

```
File::delete()
```

***

### `Space::filePrivacy()`
Method removed. Use `File::makePublic()` or `File::makePrivate()` instead.

***

### `Space::setCORS()`
Method removed. Use `Space::addCORSOrigin()`, `Space::removeCORSOrigin()` or `Space::removeAllCORSOrigins()` instead

### `Space::getLifecycleRules()`
Removed with no replacement.

### `Space::setLifecycleRules()`

Removed with no replacement.
