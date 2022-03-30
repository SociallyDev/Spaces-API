<?php

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use SpacesAPI\Exceptions\FileDoesntExistException;
use SpacesAPI\Exceptions\SpaceDoesntExistException;
use SpacesAPI\File;
use SpacesAPI\Spaces;

class SpaceTest extends TestCase
{
    private static $tempSpaceName;
    private static $space;

    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/..");
        $dotenv->load();
        $dotenv->required(['SPACES_KEY', 'SPACES_SECRET']);

        $spaces = new Spaces($_ENV['SPACES_KEY'], $_ENV['SPACES_SECRET']);

        self::$tempSpaceName = md5(time());

        self::$space = $spaces->create(self::$tempSpaceName);
    }

    public static function tearDownAfterClass(): void
    {
        self::$space->destroy();
    }

    public function testCanUpdateSpacePrivacy()
    {
        $this->assertFalse(self::$space->isPublic());

        self::$space->makePublic();
        $this->assertTrue(self::$space->isPublic());

        self::$space->makePrivate();
        $this->assertFalse(self::$space->isPublic());
    }

    public function testCanAddCORSRule()
    {
        $this->assertNull(self::$space->getCORS());

        self::$space->addCORSOrigin("http://example.com", ['GET', 'PUT'], 3200, ['custom-header']);

        $cors = self::$space->getCORS();
        $this->assertIsArray($cors);
        $this->assertEquals('custom-header', $cors[0]['AllowedHeaders'][0]);
        $this->assertEquals('GET', $cors[0]['AllowedMethods'][0]);
        $this->assertEquals('PUT', $cors[0]['AllowedMethods'][1]);
        $this->assertEquals('http://example.com', $cors[0]['AllowedOrigins'][0]);
        $this->assertEquals(3200, $cors[0]['MaxAgeSeconds']);

        self::$space->removeCORSOrigin('http://example.com');
        $this->assertNull(self::$space->getCORS());
    }

    public function testFileDoesntExistException()
    {
        $this->expectException(FileDoesntExistException::class);
        self::$space->file("non-existent.txt");
    }

    public function testCanUploadText()
    {
        $file = self::$space->uploadText("Lorem ipsum", "lorem-ipsum.txt");
        $this->assertInstanceOf(File::class, $file);
        $this->assertFalse($file->isPublic());
    }

    public function testCanPublicUploadText()
    {
        $file = self::$space->uploadText("Lorem ipsum", "lorem-ipsum.txt", [], false);
        $this->assertInstanceOf(File::class, $file);
        $this->assertTrue($file->isPublic());
    }

    public function testCanUploadFile()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'spaces-test');
        $file = self::$space->uploadFile($tmpFile, 'upload-test.txt');
        $this->assertInstanceOf(File::class, $file);
    }

    public function testCanUploadFileWithMimeType()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'spaces-test');
        $file = self::$space->uploadFile($tmpFile, 'upload-test.txt', 'text/plain');
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('text/plain', $file->content_type);
    }

    /**
     * @depends testCanUploadText
     * @depends testCanUploadFile
     */
    public function testFileExists()
    {
        $file = self::$space->file('lorem-ipsum.txt');
        $this->assertInstanceOf(File::class, $file);

        $file = self::$space->file('upload-test.txt');
        $this->assertInstanceOf(File::class, $file);
    }

    /**
     * @depends testCanUploadText
     * @depends testCanUploadFile
     */
    public function testCanListFiles()
    {
        $files = self::$space->listFiles()['files'];
        $this->assertIsArray($files);
        $this->assertCount(2, $files);
        $this->assertInstanceOf(File::class, $files[array_key_first($files)]);

        foreach ($files as $filename => $file) {
            $this->assertEquals($file->filename, $filename);
        }

        foreach ($files as $file) {
            $file->delete();
        }
    }

    public function testCanUploadDirectory()
    {
        $localDirectory = sys_get_temp_dir() . "/spaces-upload-test";
        @mkdir($localDirectory);
        for($i=1; $i<=10; $i++) {
            file_put_contents("$localDirectory/test-$i.txt", "Lorem ipsum $i");
        }

        self::$space->uploadDirectory($localDirectory, 'remote-dir');

        $list = self::$space->listFiles()['files'];
        $this->assertIsArray($list);
        $this->assertCount(10, $list);

        for ($i = 1; $i <= 10; $i++) {
            unlink("$localDirectory/test-$i.txt");
        }

        $this->assertCount(2, scandir($localDirectory));
    }

    /**
     * @depends testCanUploadDirectory
     */
    public function testCanDownloadDirectory()
    {
        $localDirectory = sys_get_temp_dir() . "/spaces-upload-test";
        $this->assertCount(2, scandir($localDirectory));

        self::$space->downloadDirectory($localDirectory, 'remote-dir');

        $this->assertCount(12, scandir($localDirectory));

        for ($i = 1; $i <= 10; $i++) {
            unlink("$localDirectory/test-$i.txt");
        }

        $this->assertCount(2, scandir($localDirectory));
    }

    public function testCanDeleteDirectory()
    {
        $list = self::$space->listFiles()['files'];
        $this->assertIsArray($list);
        $this->assertCount(10, $list);

        self::$space->deleteDirectory('remote-dir');

        $list = self::$space->listFiles()['files'];
        $this->assertIsArray($list);
        $this->assertCount(0, $list);
    }
}
