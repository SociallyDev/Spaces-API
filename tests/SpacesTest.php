<?php

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use SpacesAPI\Exceptions\AuthenticationException;
use SpacesAPI\Exceptions\SpaceDoesntExistException;
use SpacesAPI\Exceptions\SpaceExistsException;
use SpacesAPI\Space;
use SpacesAPI\Spaces;

class SpacesTest extends TestCase
{
    private static $tempSpaceName;

    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/..");
        $dotenv->load();
        $dotenv->required(['SPACES_KEY', 'SPACES_SECRET']);

        // This should hopefully always be unique amongst all DO spaces
        self::$tempSpaceName = md5(time());
    }

    public static function tearDownAfterClass(): void
    {
        (new Spaces($_ENV['SPACES_KEY'], $_ENV['SPACES_SECRET']))->space(self::$tempSpaceName)->destroy();
    }

    public function testAuthenticationCanFail()
    {
        $this->expectException(AuthenticationException::class);
        new Spaces("fake", "fake");
    }

    public function testCanAuthenticate()
    {
        $this->expectNotToPerformAssertions();
        return new Spaces($_ENV['SPACES_KEY'], $_ENV['SPACES_SECRET']);
    }

    /**
     * @depends testCanAuthenticate
     */
    public function testCreateSpaceFailsWithExistingSpace(Spaces $spaces)
    {
        $this->expectException(SpaceExistsException::class);
        $spaces->create('test');
    }

    /**
     * @depends testCanAuthenticate
     */
    public function testCanCreateSpace(Spaces $spaces)
    {
        $space = $spaces->create(self::$tempSpaceName);
        $this->assertInstanceOf(Space::class, $space);

        return $space;
    }

    /**
     * @depends testCanAuthenticate
     */
    public function testCanListSpaces(Spaces $spaces)
    {
        $list = $spaces->list();
        $this->assertIsArray($list);

        $spaceFound = false;
        foreach ($list as $name => $space) {
            if ($name == self::$tempSpaceName && $space->getName() == self::$tempSpaceName) {
                $spaceFound = true;
            }
        }

        $this->assertTrue($spaceFound);
    }

    /**
     * @depends testCanAuthenticate
     */
    public function testUseSpaceFailsWithNonExistentSpace(Spaces $spaces)
    {
        $this->expectException(SpaceDoesntExistException::class);
        $spaces->space(md5(time()));
    }
}
