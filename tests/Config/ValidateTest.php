<?php

namespace Test\Config;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use MockFileSystem\MockFileSystem as mockfs;
use App\Config\Validate;
use App\Config;
use App\Exception\ConfigException;

#[CoversClass(Validate::class)]
#[UsesClass(Config::class)]
#[UsesClass(ConfigException::class)]
#[UsesClass(\App\Config\Base::class)]
#[UsesClass(\App\Helper\Validate::class)]
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
        putenv('BVRSS_TIMEZONE');
        putenv('BVRSS_DATE_FORMAT');
        putenv('BVRSS_TIME_FORMAT');
        putenv('BVRSS_DISABLE_CACHE');
        putenv('BVRSS_ENABLE_CACHE_VIEWER');
        putenv('BVRSS_ENABLE_IMAGE_PROXY');
        putenv('BVRSS_RAW_API_ERRORS');
    }

    public function tearDown(): void
    {
        stream_context_set_default(
            [
                'mfs' => [
                    'mkdir_fail' => false
                ]
            ]
        );
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

    /**
     * Test `timezone()`
     */
    public function testTimezone(): void
    {
        putenv('BVRSS_TIMEZONE=Europe/London');

        $validate = new Validate(self::$defaults);
        $validate->timezone();
        $config = $validate->getConfig();

        $this->assertEquals('Europe/London', $config['TIMEZONE']);
    }

    /**
     * Test `timezone()` with empty timezone
     */
    public function testEmptyTimezone(): void
    {
        putenv('BVRSS_TIMEZONE=');

        $validate = new Validate(self::$defaults);
        $validate->timezone();
        $config = $validate->getConfig();

        $this->assertEquals(self::$defaults['TIMEZONE'], $config['TIMEZONE']);
    }

    /**
     * Test `timezone()` with invalid timezone
     */
    public function testInvalidTimezone(): void
    {
        putenv('BVRSS_TIMEZONE=Europe/Coventry');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid timezone given');

        $validate = new Validate(self::$defaults);
        $validate->timezone();
    }

    /**
     * Test `dateFormat()`
     */
    public function testDateFormat(): void
    {
        putenv('BVRSS_DATE_FORMAT=c');

        $validate = new Validate(self::$defaults);
        $validate->dateFormat();
        $config = $validate->getConfig();

        $this->assertEquals('c', $config['DATE_FORMAT']);
    }

    /**
     * Test `timeFormat()`
     */
    public function testTimeFormat(): void
    {
        putenv('BVRSS_TIME_FORMAT=G:i:s');

        $validate = new Validate(self::$defaults);
        $validate->timeFormat();
        $config = $validate->getConfig();

        $this->assertEquals('G:i:s', $config['TIME_FORMAT']);
    }

    /**
     * Test `cache()` with `BVRSS_DISABLE_CACHE=true`
     */
    public function testDisableCache(): void
    {
        putenv('BVRSS_DISABLE_CACHE=true');

        $validate = new Validate(self::$defaults);
        $validate->cache();
        $config = $validate->getConfig();

        $this->assertTrue($config['DISABLE_CACHE']);
    }

    /**
     * Test `cache()` with `BVRSS_DISABLE_DIR`
     */
    public function testCacheDir(): void
    {
        mockfs::create();
        $folder = mockfs::getUrl('/cache');

        mkdir($folder);

        putenv('BVRSS_CACHE_DIR=' . $folder);

        $validate = new Validate(self::$defaults);
        $validate->cache();
        $config = $validate->getConfig();

        $this->assertEquals($folder, $config['CACHE_DIR']);
    }

    /**
     * Test cache folder creation failure
     */
    public function testCacheDirCreationFailure(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Could not create cache directory [BVRSS_CACHE_DIR]');

        mockfs::create();
        $folder = mockfs::getUrl('/data');

        stream_context_set_default(
            [
                'mfs' => [
                    'mkdir_fail' => true,
                ]
            ]
        );

        putenv('BVRSS_CACHE_DIR=' . $folder);

        $validate = new Validate(self::$defaults);
        $validate->cache();
    }

    /**
     * Test `cacheViewer()`
     */
    public function testCacheViewer(): void
    {
        putenv('BVRSS_ENABLE_CACHE_VIEWER=true');

        $validate = new Validate(self::$defaults);
        $validate->cacheViewer();
        $config = $validate->getConfig();

        $this->assertTrue($config['ENABLE_CACHE_VIEWER']);
    }

    /**
     * Test `imageProxy()`
     */
    public function testImageProxy(): void
    {
        putenv('BVRSS_ENABLE_IMAGE_PROXY=true');

        $validate = new Validate(self::$defaults);
        $validate->imageProxy();
        $config = $validate->getConfig();

        $this->assertTrue($config['ENABLE_IMAGE_PROXY']);
    }

    /**
     * Test `cspStatus()`
     */
    public function testCspStatus(): void
    {
        putenv('BVRSS_DISABLE_CSP=true');

        $validate = new Validate(self::$defaults);
        $validate->cspStatus();
        $config = $validate->getConfig();

        $this->assertTrue($config['DISABLE_CSP']);
    }

    /**
     * Test `rawApiErrors()`
     */
    public function testRawApiErrors(): void
    {
        putenv('BVRSS_RAW_API_ERRORS=true');

        $validate = new Validate(self::$defaults);
        $validate->rawApiErrors();
        $config = $validate->getConfig();

        $this->assertTrue($config['RAW_API_ERRORS']);
    }
}
