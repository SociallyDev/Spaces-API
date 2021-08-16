# SpacesAPI\Spaces

Represents the connection to Digital Ocean spaces.

The entry point for managing spaces.

Instantiate your connection with `new \SpacesAPI\Spaces("access-key", "secret-key", "region")`

Obtain your access and secret keys from the [DigitalOcean Applications & API dashboard](https://cloud.digitalocean.com/account/api/tokens)





## Methods

| Name | Description |
|------|-------------|
|[__construct](#spaces__construct)|Initialise the API|
|[create](#spacescreate)|Create a new space|
|[list](#spaceslist)|List all your spaces|
|[space](#spacesspace)|Use an existing space|




### Spaces::__construct

**Description**

```php
public __construct (string $accessKey, string $secretKey, string $region = "ams3", string $host = "digitaloceanspaces.com)
```

Initialise the API



**Parameters**

* `(string) $accessKey`
: Digital Ocean API access key
* `(string) $secretKey`
: Digital Ocean API secret key
* `(string) $region`
: Region, defaults to `ams3`
* `(string) $host`
: API endpoint, defaults to `digitaloceanspaces.com`

**Return Values**

`void`


**Throws Exceptions**


`\SpacesAPI\Exceptions\AuthenticationException`
> Authentication failed

<hr />


### Spaces::create

**Description**

```php
public create (string $name, bool $public = false)
```

Create a new space



**Parameters**

* `(string) $name`
: The name of the new space
* `(bool) $public`
: Enable file listing. Default `false`

**Return Values**

`\SpacesAPI\Space`

> The newly created space


**Throws Exceptions**


`\SpacesAPI\Exceptions\SpaceExistsException`
> The named space already exists

<hr />


### Spaces::list

**Description**

```php
public list (void)
```

List all your spaces



**Parameters**

`This function has no parameters.`

**Return Values**

`array`

> An array of `\SpacesAPI\Space` instances indexed by the space name


<hr />


### Spaces::space

**Description**

```php
public space (string $name)
```

Use an existing space



**Parameters**

* `(string) $name`
: The name of the space

**Return Values**

`\SpacesAPI\Space`

> The loaded space


**Throws Exceptions**


`\SpacesAPI\Exceptions\SpaceDoesntExistException`
> The named space doesn't exist

<hr />

