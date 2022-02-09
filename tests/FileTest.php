<?php

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use SpacesAPI\Exceptions\FileDoesntExistException;
use SpacesAPI\Exceptions\SpaceDoesntExistException;
use SpacesAPI\Spaces;

class FileTest extends TestCase
{
    private static $tempSpaceName;
    private static $space;
    private static $file;

    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/..");
        $dotenv->load();
        $dotenv->required(['SPACES_KEY', 'SPACES_SECRET']);

        $spaces = new Spaces($_ENV['SPACES_KEY'], $_ENV['SPACES_SECRET']);

        self::$tempSpaceName = md5(time());

        self::$space = $spaces->create(self::$tempSpaceName);
        self::$file = self::$space->uploadText('Lorem ipsum', 'lorem-ipsum.txt');
    }

    public static function tearDownAfterClass(): void
    {
        self::$space->destroy();
    }

    public function testCanUpdatePrivacy()
    {
        $this->assertFalse(self::$file->isPublic());

        self::$file->makePublic();
        $this->assertTrue(self::$file->isPublic());

        self::$file->makePrivate();
        $this->assertFalse(self::$file->isPublic());
    }

    public function testCanGetContents()
    {
        $this->assertEquals("Lorem ipsum", self::$file->getContents());
    }

    public function testCanDownloadFile()
    {
        $filename = sys_get_temp_dir() . "/lorem.txt";
        self::$file->download($filename);

        $this->assertEquals("Lorem ipsum", file_get_contents($filename));
    }

    public function testCanCopyFile()
    {
        $this->expectNotToPerformAssertions();
        self::$file->copy('lorem-ipsum-2.txt');
        self::$space->file('lorem-ipsum-2.txt');
    }

    public function testCanMoveRenameFile()
    {
        $file = self::$file->copy('test.txt');
        $file->move('renamed-file.txt');

        $this->assertEquals("renamed-file.txt", $file->filename);

        $this->expectException(FileDoesntExistException::class);
        self::$space->file('test.txt');
    }

    public function testCanGetURL()
    {
        $this->assertStringContainsString('lorem-ipsum.txt', self::$file->getURL());
        $this->assertStringContainsString('lorem-ipsum.txt', self::$file->getSignedURL());
    }

    public function testCanDeleteFile()
    {
        self::$file->delete();

        $this->expectException(FileDoesntExistException::class);
        self::$space->file('lorem-ipsum.txt');
    }
}
