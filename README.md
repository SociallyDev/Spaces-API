![Spaces-API](https://imgur.com/NYNsQyl.png "Devang Srivastava's Spaces-API")

PHP library for accessing Digital Ocean spaces

## Installation
Install via composer
```
composer require sociallydev/spaces-api
```

## Quick start

Obtain API keys from the [Digital Ocean Applications & API dashboard](https://cloud.digitalocean.com/account/api/tokens)

```php
use SpacesAPI\Spaces;

// Connect to a space
$spaces = new Spaces('api-key', 'api-secret');
$space = $spaces->space('space-name');

// Download a file
$file = $space->file('remote-file-1.txt');
$file->download('local/file/path/file.txt');

// Upload text to a file
$file2 = $space->uploadText("Lorem ipsum","remote-file-2.txt");

// Get a signed public link, valid for 2 hours
$file2url = $file2->getSignedURL("2 hours");

// Make a copy
$file3 = $file2->copy('remote-file-3.txt');

// Move or rename a file
$file2->move('new-filename.txt')

// Make a file public and get the URL
$file3->makePublic();
$file3url = $file3->getURL();
```

See more examples in [docs/Examples.md](docs/Examples.md)

## Upgrading?
Version 3 has many changes over version 2, so we have written a [migration guide](docs/Upgrade2-3.md)

## API reference
* [\SpacesAPI\Spaces](docs/Spaces.md)
* [\SpacesAPI\Space](docs/Space.md)
* [\SpacesAPI\File](docs/File.md)
