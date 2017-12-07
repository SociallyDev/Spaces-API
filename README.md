# Spaces-API
An API wrapper for DigitalOcean's Spaces object storage designed for easy use. 

&nbsp;

### Connecting:
```php
require_once("spaces.php");

$key = "EXAMPLE_KEY";
$secret = "EXAMPLE_SECRET";

$space = new SpacesConnect($key, $secret);
```

All available configurations: <br>
###### SpacesConnect( REQUIRED KEY, REQUIRED SECRET, OPTIONAL SPACE's NAME, OPTIONAL REGION, OPTION HOST DOMAIN );
