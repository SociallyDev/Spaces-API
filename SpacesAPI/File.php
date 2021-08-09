<?php

namespace SpacesAPI;

use SpacesAPI\Exceptions\FileDoesntExistException;

/**
 * Represents a single file
 *
 * You wouldn't normally instantiate this class directly,
 * Rather obtain an instance from `\SpacesAPI\Space::list()`, `\SpacesAPI\Spaces::file()`, `\SpacesAPI\Spaces::uploadText()` or `\SpacesAPI\Spaces::uploadFile()`
 *
 * @property string $expiration
 * @property string $e_tag
 * @property int $last_modified
 * @property string $content_type
 * @property int $content_length
 */
class File
{
    use StringFunctions;

    /**
     * @var \SpacesAPI\Space
     */
    private $space;

    /**
     * The name of the current space
     *
     * @var string
     */
    private $space_name;

    /**
     * @var \Aws\S3\S3Client
     */
    private $s3;

    private $_expiration;
    private $_e_tag;
    private $_filename;
    private $_last_modified;
    private $_content_type;
    private $_content_length;

    /**
     * @param \SpacesAPI\Space $space
     * @param string $filename
     * @param array $info
     *
     * @throws \SpacesAPI\Exceptions\FileDoesntExistException
     */
    public function __construct(Space $space, string $filename, array $info = [])
    {
        $this->space = $space;
        $this->space_name = $space->getName();
        $this->s3 = $space->getS3Client();
        $this->_filename = $filename;

        if (!$this->s3->doesObjectExist($this->space_name, $filename)) {
            throw new FileDoesntExistException("File $filename doesn't exist");
        }

        if (count($info) > 0) {
            $this->setFileInfo($info);
        }
    }

    /**
     * Magic getter to make the properties read-only
     *
     * @param string $name
     *
     * @return null
     */
    public function __get(string $name)
    {
        if (!property_exists($this, "_$name")) {
            trigger_error("Undefined property: SpacesAPI\File::$name", E_USER_NOTICE);
            return null;
        }

        if (!$this->{"_$name"}) {
            $this->fetchFileInfo();
        }

        return $this->{"_$name"};
    }

    /**
     * @param array $info
     */
    private function setFileInfo(array $info): void
    {
        foreach ($info as $_property => $value) {
            $property = "_" . $this->pascalCaseToCamelCase($_property);

            if ($property == 'size') {
                $property = 'content_length';
            }

            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    /**
     *
     */
    private function fetchFileInfo(): void
    {
        $this->setFileInfo(
            Result::parse(
                $this->s3->headObject([
                                          "Bucket" => $this->space_name,
                                          "Key" => $this->_filename,
                                      ])
            )
        );
    }

    /**
     * Is this file publicly accessible
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        $acl = Result::parse(
            $this->s3->getObjectAcl([
                                        "Bucket" => $this->space_name,
                                        "Key" => $this->_filename,
                                    ])
        );

        return (
            isset($acl['Grants'][0]['Grantee']['URI']) &&
            $acl['Grants'][0]['Grantee']['URI'] == "http://acs.amazonaws.com/groups/global/AllUsers" &&
            $acl['Grants'][0]['Permission'] == "READ"
        );
    }

    /**
     * Make a file public or privately accessible
     *
     * @param bool $public
     */
    private function updatePrivacy(bool $public): void
    {
        $this->s3->putObjectAcl([
                                    "Bucket" => $this->space_name,
                                    "Key" => $this->_filename,
                                    "ACL" => ($public) ? "public-read" : "private",
                                ]);
    }

    /**
     * Make file publicly accessible
     */
    public function makePublic(): void
    {
        $this->updatePrivacy(true);
    }

    /**
     * Make file non-publicly accessible
     */
    public function makePrivate(): void
    {
        $this->updatePrivacy(false);
    }

    /**
     * Get the file contents as a string
     *
     * @return string
     */
    public function getContents(): string
    {
        return $this->s3->getObject([
                                        "Bucket" => $this->space_name,
                                        "Key" => $this->_filename,
                                    ])["Body"]->getContents();
    }

    /**
     * Download the file to a local location
     *
     * @param string $saveAs
     *
     * @return void
     */
    public function download(string $saveAs): void
    {
        $this->s3->getObject([
                                 "Bucket" => $this->space_name,
                                 "Key" => $this->_filename,
                                 "SaveAs" => $saveAs,
                             ]);
    }

    /**
     * Copy the file on the space
     *
     * @param string $newFilename
     * @param false $public
     *
     * @return \SpacesAPI\File
     */
    public function copy(string $newFilename, bool $public = false): File
    {
        $this->s3->copy(
            $this->space_name,
            $this->_filename,
            $this->space_name,
            $newFilename,
            ($public) ? 'public-read' : 'private'
        );

        return new self($this->space, $newFilename);
    }

    /**
     * Get the public URL
     * This URL will not work if the file is private
     *
     * @return string
     * @see getSignedURL
     *
     */
    public function getURL(): string
    {
        return $this->s3->getObjectUrl($this->space_name, $this->_filename);
    }

    /**
     * Get a signed URL, which will work for private files
     *
     * @param string|\DateTime|int $validFor Can be any string recognised by strtotime(), an instance of DateTime or a unix timestamp
     *
     * @return string
     */
    public function getSignedURL($validFor = "15 minutes"): string
    {
        return (string)$this->s3->createPresignedRequest(
            $this->s3->getCommand("GetObject", [
                "Bucket" => $this->space_name,
                "Key" => $this->_filename,
            ]),
            $validFor
        )->getUri();
    }

    /**
     * Permanently delete this file
     */
    public function delete(): void
    {
        $this->s3->deleteObject([
                                    "Bucket" => $this->space_name,
                                    "Key" => $this->_filename,
                                ]);
    }
}
