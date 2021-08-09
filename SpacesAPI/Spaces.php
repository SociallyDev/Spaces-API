<?php

namespace SpacesAPI;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use SpacesAPI\Exceptions\AuthenticationException;
use SpacesAPI\Exceptions\SpaceExistsException;

/**
 * Represents the connection to Digital Ocean spaces.
 * The entry point for managing spaces.
 *
 * Instantiate your connection with `new \SpacesAPI\Spaces("access-key", "secret-key", "region")`
 *
 * Obtain your access and secret keys from the [DigitalOcean Applications & API dashboard](https://cloud.digitalocean.com/account/api/tokens)
 */
class Spaces
{
    /**
     * @var \Aws\S3\S3Client
     */
    private $s3;

    /**
     * Initialise the API
     *
     * @param string $accessKey Digital Ocean API access key
     * @param string $secretKey Digital Ocean API secret key
     * @param string $region Region, defaults to ams3
     * @param string $host API endpoint, defaults to digitaloceanspaces.com
     *
     * @throws \SpacesAPI\Exceptions\AuthenticationException Authentication failed
     */
    public function __construct(string $accessKey, string $secretKey, string $region = "ams3", string $host = "digitaloceanspaces.com")
    {
        $this->s3 = new S3Client([
                                     "version" => "latest",
                                     "region" => "us-east-1",
                                     "endpoint" => "https://$region.$host",
                                     "credentials" => ["key" => $accessKey, "secret" => $secretKey],
                                     "ua_append" => "SociallyDev-Spaces-API/2",
                                 ]);

        try {
            $this->s3->headBucket(["Bucket" => 'auth-check']);
        } catch (S3Exception $e) {
            if ($e->getStatusCode() == 403) {
                throw new AuthenticationException("Authentication failed");
            }
        }
    }

    /**
     * List all your spaces
     *
     * @return array An array of \SpacesAPI\Space instances
     */
    public function list(): array
    {
        $spaces = [];

        foreach (Result::parse($this->s3->listBuckets()['Buckets']) as $bucket) {
            $spaces[] = new Space($this->s3, $bucket['Name']);
        }

        return $spaces;
    }

    /**
     * Create a new space
     *
     * @param string $name The name of the new space
     * @param bool $public Enable file listing. Default `false`
     *
     * @return \SpacesAPI\Space The newly created space
     * @throws \SpacesAPI\Exceptions\SpaceExistsException The named space already exists
     */
    public function create(string $name, bool $public = false): Space
    {
        try {
            $this->s3->createBucket([
                                        "ACL" => ($public) ? "public-read" : "private",
                                        "Bucket" => $name,
                                    ]);
        } catch (S3Exception $e) {
            throw new SpaceExistsException($e->getAwsErrorMessage());
        }

        return new Space($this->s3, $name);
    }

    /**
     * Use an existing space
     *
     * @param string $name The name of the space
     *
     * @return \SpacesAPI\Space The loaded space
     * @throws \SpacesAPI\Exceptions\SpaceDoesntExistException The named space doesn't exist
     */
    public function space(string $name): Space
    {
        return new Space($this->s3, $name);
    }
}
