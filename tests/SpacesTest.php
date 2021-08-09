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
    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/..");
        $dotenv->load();
        $dotenv->required(['SPACES_KEY', 'SPACES_SECRET']);

        try {
            (new Spaces($_ENV['SPACES_KEY'], $_ENV['SPACES_SECRET']))->space('spaces-api-test')->destroySpace();
        } catch (SpaceDoesntExistException $e) {
        }
    }

    public static function tearDownAfterClass(): void
    {
        (new Spaces($_ENV['SPACES_KEY'], $_ENV['SPACES_SECRET']))->space('spaces-api-test')->destroySpace();
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
        $space = $spaces->create('spaces-api-test');
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
        foreach ($list as $space) {
            if ($space->getName() == 'spaces-api-test') {
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
