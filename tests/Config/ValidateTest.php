<?php

namespace Test\Config;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Config\Validate;
use App\Config;
use App\Exception\ConfigException;

#[CoversClass(Validate::class)]
#[UsesClass(Config::class)]
class ValidateTest extends TestCase
{
    /** @var array<string, mixed> $defaults */
    private static array $defaults = [];

    public static function setupBeforeClass(): void
    {
        $reflection = new \ReflectionClass(new Config());
        self::$defaults = $reflection->getProperty('config')->getValue(new Config());
    }

    public function setUp(): void
    {
        // Unset environment variables before each test
        putenv('BVRSS_SELF_URL_PATH');
        putenv('BVRSS_YOUTUBE_API_KEY');
    }

    /**
     * Test `getConfig`
     */
    public function testGetConfig(): void
    {
        $validate = new Validate(self::$defaults);
        $this->assertEquals(self::$defaults, $validate->getConfig());
    }

    /**
     * Test `version()`
     */
    public function testVersion(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('BetterVideoRss requires at least PHP version 8.1.0');

        $validate = new Validate(self::$defaults);
        $validate->version('8.0.0', '8.1.0');
    }

    /**
     * Test `extensions()`
     */
    public function testExtensions(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('PHP extension error: pgp extension not loaded');

        $validate = new Validate(self::$defaults);
        $validate->extensions(['pgp']);
    }

    /**
     * Test `apiKey()`
     */
    public function testSelfUrlPath(): void
    {
        putenv('BVRSS_SELF_URL_PATH=https://example.com/');

        $validate = new Validate(self::$defaults);
        $validate->selfUrlPath();
        $config = $validate->getConfig();

        $this->assertEquals('https://example.com/', $config['SELF_URL_PATH']);
    }

    /**
     * Test `selfUrlPath()` with no self URL path
     */
    public function testNoSelfUrlPath(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Self URL path must be set');

        $validate = new Validate(self::$defaults);
        $validate->selfUrlPath();
    }

    /**
     * Test `selfUrlPath()` with empty self URL path
     */
    public function testEmptySelfUrlPath(): void
    {
        putenv('BVRSS_SELF_URL_PATH=');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Self URL path must be set');

        $validate = new Validate(self::$defaults);
        $validate->selfUrlPath();
    }

    /**
     * Test `selfUrlPath()` with self URL path missing ending forward
     */
    public function testSelfUrlPathNoEndingForwardSlash(): void
    {
        putenv('BVRSS_SELF_URL_PATH=https://example.com');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Self URL must end with a forward slash');

        $validate = new Validate(self::$defaults);
        $validate->selfUrlPath();
    }

    /**
     * Test `selfUrlPath()` with self URL path missing HTTP protocol
     */
    public function testSelfUrlPathMissingProtocol(): void
    {
        putenv('BVRSS_SELF_URL_PATH=example.com/');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Self URL must start with http:// or https://');

        $validate = new Validate(self::$defaults);
        $validate->selfUrlPath();
    }

    /**
     * Test `apiKey()`
     */
    public function testYouTubeApiKey(): void
    {
        putenv('BVRSS_YOUTUBE_API_KEY=qwerty');

        $validate = new Validate(self::$defaults);
        $validate->apiKey();
        $config = $validate->getConfig();

        $this->assertEquals('qwerty', $config['YOUTUBE_API_KEY']);
    }

    /**
     * Test `apiKey()` with missing YouTube API key
     */
    public function testMissingYouTubeApiKey(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('YouTube API key must be set');

        $validate = new Validate(self::$defaults);
        $validate->apiKey();
    }

    /**
     * Test `apiKey()` with empty YouTube API key
     */
    public function testEmptyYouTubeApiKey(): void
    {
        putenv('BVRSS_YOUTUBE_API_KEY=');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('YouTube API key must be set');

        $validate = new Validate(self::$defaults);
        $validate->apiKey();
    }
}
