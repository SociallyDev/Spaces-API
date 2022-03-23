<?php

namespace SpacesAPI;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use SpacesAPI\Exceptions\SpaceDoesntExistException;

/**
 * Represents a space once connected/created
 *
 * You wouldn't normally instantiate this class directly,
 * Rather obtain an instance from `\SpacesAPI\Spaces::space()` or `\SpacesAPI\Spaces::create()`
 */
class Space
{
    /**
     * AWS S3 client
     *
     * @var \Aws\S3\S3Client
     */
    private $s3;

    /**
     * The name of the current space
     *
     * @var string
     */
    private $name;

    /**
     * Load a space
     *
     * You wouldn't normally call this directly,
     * rather obtain an instance from `\SpacesAPI\Spaces::space()` or `\SpacesAPI\Spaces::create()`
     *
     * @param \Aws\S3\S3Client $s3 An authenticated S3Client instance
     * @param string $name Space name
     * @param bool $validate Check that the space exists
     *
     * @throws \SpacesAPI\Exceptions\SpaceDoesntExistException If validation is `true` and the space doesn't exist
     */
    public function __construct(S3Client $s3, string $name, bool $validate = true)
    {
        $this->s3 = $s3;
        $this->name = $name;

        if ($validate && !$this->s3->doesBucketExist($name)) {
            throw new SpaceDoesntExistException("Space '$this->name' does not exist");
        }
    }

    /**
     * Get the current AWS S3 client instance
     *
     * For internal library use
     *
     * @return \Aws\S3\S3Client
     */
    public function getS3Client(): S3Client
    {
        return $this->s3;
    }

    /**
     * Get the name of this space
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Update space privacy
     *
     * @param bool $public
     */
    private function updatePrivacy(bool $public): void
    {
        $this->s3->putBucketAcl([
                                    "Bucket" => $this->name,
                                    "ACL" => ($public) ? "public-read" : "private",
                                ]);
    }

    /**
     * Enable file listing
     */
    public function makePublic(): void
    {
        $this->updatePrivacy(true);
    }

    /**
     * Disable file listing
     */
    public function makePrivate(): void
    {
        $this->updatePrivacy(false);
    }

    /**
     * Is file listing enabled?
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        $acl = Result::parse($this->s3->getBucketAcl(["Bucket" => $this->name]));

        return (
            isset($acl['Grants'][0]['Grantee']['URI']) &&
            $acl['Grants'][0]['Grantee']['URI'] == "http://acs.amazonaws.com/groups/global/AllUsers" &&
            $acl['Grants'][0]['Permission'] == "READ"
        );
    }

    /**
     * Destroy/Delete this space, along with all files
     */
    public function destroy(): void
    {
        $this->s3->deleteMatchingObjects($this->name, "", "(.*?)");
        $this->s3->deleteBucket(["Bucket" => $this->name]);
    }

    /**
     * Get the CORS configuration for the space
     *
     * @return array|null An array of CORS rules or null if no rules exist
     */
    public function getCORS(): ?array
    {
        try {
            return Result::parse(
                $this->s3->getBucketCors([
                                             "Bucket" => $this->name,
                                         ])
            )['CORSRules'];
        } catch (S3Exception $e) {
            return null;
        }
    }

    /**
     * Get the CORS rules, removing the origin specified
     *
     * @param string $origin
     *
     * @return array
     */
    private function getCORSRemovingOrigin(string $origin): array
    {
        if (!$CORSRules = $this->getCORS()) {
            return [];
        }

        foreach ($CORSRules as $i => $cors) {
            if ($cors['AllowedOrigins'][0] == $origin) {
                array_splice($CORSRules, $i, 1);
            }
        }

        return $CORSRules;
    }

    /**
     * Set the CORS rules
     *
     * @param array $rules
     */
    private function putCORS(array $rules): void
    {
        $this->s3->putBucketCors([
                                     "Bucket" => $this->name,
                                     "CORSConfiguration" => [
                                         "CORSRules" => $rules,
                                     ],
                                 ]);
    }

    /**
     * Add an origin to the CORS settings on this space
     *
     * @param string $origin eg `http://example.com`
     * @param array $methods Array items must be one of `GET`, `PUT`, `DELETE`, `POST` and `HEAD`
     * @param int $maxAge Access Control Max Age
     * @param array $headers Allowed Headers
     */
    public function addCORSOrigin(string $origin, array $methods, int $maxAge = 0, array $headers = []): void
    {
        $rules = $this->getCORSRemovingOrigin($origin);

        $this->putCORS(
            array_merge($rules, [
                [
                    "AllowedHeaders" => $headers,
                    "AllowedMethods" => $methods,
                    "AllowedOrigins" => [$origin],
                    "MaxAgeSeconds" => $maxAge,
                ],
            ])
        );
    }

    /**
     * Remove an origin from the CORS settings on this space
     *
     * @param string $origin eg `http://example.com`
     */
    public function removeCORSOrigin(string $origin): void
    {
        $rules = $this->getCORSRemovingOrigin($origin);

        if (empty($rules)) {
            $this->removeAllCORSOrigins();
        } else {
            $this->putCORS($rules);
        }
    }

    /**
     * Delete all CORS rules
     */
    public function removeAllCORSOrigins(): void
    {
        $this->s3->deleteBucketCors([
                                        'Bucket' => $this->name,
                                    ]);
    }

    /**
     * List all files in the space (recursively)
     *
     * @param string $directory The directory to list files in. Empty string for root directory
     *
     * @return array
     */
    public function listFiles(string $directory = ""): array
    {
        $rawFiles = $this->rawListFiles($directory);
        $files = [];

        foreach ($rawFiles as $fileInfo) {
            $files[$fileInfo['Key']] = new File($this, $fileInfo['Key'], $fileInfo, false);
        }

        return ['files' => $files];
    }

    /**
     * @param string $directory The directory to list files in. Empty string for root directory
     * @param string|null $continuationToken Used internally to work around request limits (1000 files per request)
     *
     * @return array
     */
    private function rawListFiles(string $directory = "", ?string $continuationToken = null): array
    {
        $data = Result::parse(
            $this->s3->listObjectsV2([
                                         "Bucket" => $this->name,
                                         "Prefix" => $directory,
                                         "MaxKeys" => 1000,
//                                         "StartAfter" => 0, // For skipping files, maybe for future limit/skip ability
                                         "FetchOwner" => false,
                                         "ContinuationToken" => $continuationToken,
                                     ])
        );

        if (!isset($data['Contents'])) {
            return [];
        }

        $files = $data['Contents'];

        if (isset($data["NextContinuationToken"]) && $data["NextContinuationToken"] != "") {
            $files = array_merge($files, $this->rawListFiles($directory, $data["NextContinuationToken"]));
        }

        return $files;
    }

    /**
     * Upload a string of text to file
     *
     * @param string $text The text to upload
     * @param string $filename The filepath/name to save to
     * @param array $params Any extra parameters. [See here](https://docs.aws.amazon.com/AWSJavaScriptSDK/latest/AWS/S3.html#upload-property)
     * @param bool $private True for the file to be private, false to allow public access
     *
     * @return \SpacesAPI\File
     */
    public function uploadText(string $text, string $filename, array $params = [], bool $private = true): File
    {
        $this->s3->upload($this->name, $filename, $text, ($private) ? 'private' : 'public-read', $params);
        return new File($this, $filename, [], false);
    }

    /**
     * Upload a file
     *
     * @param string $filepath The path to the file, including the filename. Relative and absolute paths are accepted.
     * @param string|null $filename The remote filename. If `null`, the local filename will be used.
     * @param string|null $mimeType The file mime type to pass as ContentType for the file (e.g. 'image/jpeg').
     * @param bool $private True for the file to be private, false to allow public access.
     *
     * @return \SpacesAPI\File
     */
    public function uploadFile(string $filepath, ?string $filename = null, ?string $mimeType = null, bool $private = true): File
    {
        $this->s3->putObject([
                                 'Bucket' => $this->name,
                                 'Key' => ($filename) ?: basename($filepath),
                                 'SourceFile' => $filepath,
                                 'ContentType' => $mimeType,
                                 'ACL' => ($private) ? 'private' : 'public-read'
                             ]);

        return new File($this, ($filename) ?: basename($filepath), [], false);
    }

    /**
     * Get an instance of \SpacesAPI\File for a given filename
     *
     * @param string $filename
     *
     * @return \SpacesAPI\File
     * @throws \SpacesAPI\Exceptions\FileDoesntExistException Thrown if the file doesn't exist
     */
    public function file(string $filename): File
    {
        return new File($this, $filename);
    }

    /**
     * Recursively upload an entire directory
     *
     * @param string $local The local directory to upload
     * @param string|null $remote The remote directory to place the files in. `null` to place in the root
     */
    public function uploadDirectory(string $local, ?string $remote = null): void
    {
        $this->s3->uploadDirectory($local, $this->name, $remote);
    }

    /**
     * Recursively download an entire directory.
     *
     * @param string $local The local directory to save the directories/files in
     * @param string|null $remote The remote directory to download. `null` to download the entire space
     */
    public function downloadDirectory(string $local, ?string $remote = null): void
    {
        $this->s3->downloadBucket($local, $this->name, $remote);
    }

    /**
     * Delete an entire directory, including its contents
     *
     * @param string $path The directory to delete
     */
    public function deleteDirectory(string $path): void
    {
        $this->s3->deleteMatchingObjects($this->name, $path);
    }
}
