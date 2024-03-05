<?php

use PHPUnit\Framework\TestCase;
use App\Config;
use App\Exception\ConfigException;

class ConfigTest extends TestCase
{
    public function setUp(): void
    {
        // Unset environment variables before each test
        putenv('BVRSS_SELF_URL_PATH');
        putenv('BVRSS_YOUTUBE_API_KEY');

        putenv('BVRSS_CACHE_DIR');
        putenv('BVRSS_DISABLE_CACHE');
        putenv('BVRSS_ENABLE_CACHE_VIEWER');
        putenv('BVRSS_DISABLE_CSP');
        putenv('BVRSS_ENABLE_IMAGE_PROXY');
        putenv('BVRSS_TIMEZONE');
        putenv('BVRSS_DATE_FORMAT');
        putenv('BVRSS_TIME_FORMAT');
        putenv('BVRSS_RAW_API_ERRORS');
    }

    /**
     * Test `getCsp()`
     */
    public function testGetCsp(): void
    {
        $config = new Config();
        $this->assertStringContainsString("default-src 'self';", $config->getCsp());
    }

    /**
     * Test `getCspDisabledStatus()`
     */
    public function testGetCspDisabledStatus(): void
    {
        putenv('BVRSS_DISABLE_CSP=true');

        $config = new Config();
        $config->checkOptional();

        $this->assertTrue($config->getCspDisabledStatus());
    }

    /**
     * Test `getVersion()`
     */
    public function testGetVersion(): void
    {
        $config = new Config();
        $this->assertEquals(constant('VERSION'), $config->getVersion());
    }

    /**
     * Test `getUseragent()`
     */
    public function testGetUseragent(): void
    {
        $useragent = sprintf(
            'BetterVideoRss/%s (+https://github.com/VerifiedJoseph/BetterVideoRss)',
            constant('VERSION')
        );

        $config = new Config();
        $this->assertEquals($useragent, $config->getUseragent());
    }

    /**
     * Test `getDefaultFeedFormat()`
     */
    public function testGetDefaultFeedFormat(): void
    {
        $config = new Config();
        $this->assertEquals('rss', $config->getDefaultFeedFormat());
    }

    /**
     * Test `getFeedFormats()`
     */
    public function tesGetFeedFormats(): void
    {
        $config = new Config();
        $this->assertNotEmpty($config->getFeedFormats());
    }

    /**
     * Test `getCacheDirPath()`
     */
    public function testGetCacheDirPath(): void
    {
        putenv('BVRSS_CACHE_DIR=cache');

        $config = new Config();
        $config->checkCache();

        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cache';
        $this->assertEquals($path, $config->getCacheDirPath());
    }

    /**
     * Test `getSelfUrl()`
     */
    public function testGetSelfUrl(): void
    {
        putenv('BVRSS_SELF_URL_PATH=https://example.com/');
        putenv('BVRSS_YOUTUBE_API_KEY=fake-key');

        $config = new Config();
        $config->checkConfig();

        $this->assertEquals('https://example.com/', $config->getSelfUrl());
    }

    /**
     * Test `getCacheDirectory()`
     */
    public function testGetCacheDirectory(): void
    {
        putenv('BVRSS_CACHE_DIR=cache');

        $config = new Config();
        $config->checkCache();

        $this->assertEquals('cache', $config->getCacheDirectory());
    }

    /**
     * Test `getCacheDisableStatus()`
     */
    public function testGetCacheDisableStatus(): void
    {
        putenv('BVRSS_DISABLE_CACHE=true');

        $config = new Config();
        $config->checkCache();

        $this->assertTrue($config->getCacheDisableStatus());
    }

    /**
     * Test `getCacheViewerStatus()`
     */
    public function testGetCacheViewerStatus(): void
    {
        putenv('BVRSS_ENABLE_CACHE_VIEWER=true');

        $config = new Config();
        $config->checkCache();

        $this->assertTrue($config->getCacheViewerStatus());
    }

    /**
     * Test `getCacheFormatVersion()`
     */
    public function testGetCacheFormatVersion(): void
    {
        $config = new Config();
        $this->assertGreaterThan(0, $config->getCacheFormatVersion());
    }

    /**
     * Test `getImageProxyStatus()`
     */
    public function testGetImageProxyStatus(): void
    {
        putenv('BVRSS_ENABLE_IMAGE_PROXY=true');

        $config = new Config();
        $config->checkOptional();

        $this->assertTrue($config->getImageProxyStatus());
    }

    /**
     * Test `getApiKey()`
     */
    public function testGetApiKey(): void
    {
        putenv('BVRSS_SELF_URL_PATH=https://example.com/');
        putenv('BVRSS_YOUTUBE_API_KEY=i3CAOMsuGbaP3aKttQf');

        $config = new Config();
        $config->checkConfig();

        $this->assertEquals('i3CAOMsuGbaP3aKttQf', $config->getApiKey());
    }

    /**
     * Test `getTimezone()`
     */
    public function testGetTimezone(): void
    {
        putenv('BVRSS_TIMEZONE=Europe/London');

        $config = new Config();
        $config->checkOptional();

        $this->assertEquals('Europe/London', $config->getTimezone());
    }

    /**
     * Test `getDateFormat()`
     */
    public function testGetDateFormat(): void
    {
        putenv('BVRSS_DATE_FORMAT=c');

        $config = new Config();
        $config->checkOptional();

        $this->assertEquals('c', $config->getDateFormat());
    }

    /**
     * Test `getTimeFormat()`
     */
    public function testGetTimeFormat(): void
    {
        putenv('BVRSS_TIME_FORMAT=G:i:s');

        $config = new Config();
        $config->checkOptional();

        $this->assertEquals('G:i:s', $config->getTimeFormat());
    }

    /**
     * Test `getRawApiErrorStatus()`
     */
    public function testGetRawApiErrorStatus(): void
    {
        putenv('BVRSS_RAW_API_ERRORS=true');

        $config = new Config();
        $config->checkOptional();

        $this->assertTrue($config->getRawApiErrorStatus());
    }

    /**
     * Test with no `BVRSS_SELF_URL_PATH`
     */
    public function testWithNoSelfUrlPath(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Self URL path must be set');

        $config = new Config();
        $config->checkConfig();
    }

    /**
     * Test with empty `BVRSS_SELF_URL_PATH`
     */
    public function testWithEmptySelfUrlPath(): void
    {
        putenv('BVRSS_SELF_URL_PATH=');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Self URL path must be set');

        $config = new Config();
        $config->checkConfig();
    }

    /**
     * Test `BVRSS_SELF_URL_PATH` with missing ending forward slash
     */
    public function testWithSelfUrlPathNoEndingForwardSlash(): void
    {
        putenv('BVRSS_SELF_URL_PATH=https://example.com');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Self URL must end with a forward slash');

        $config = new Config();
        $config->checkConfig();
    }

    /**
     * Test `BVRSS_SELF_URL_PATH` with missing HTTP protocol
     */
    public function testWithSelfUrlPathMissingProtocol(): void
    {
        putenv('BVRSS_SELF_URL_PATH=example.com/');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Self URL must start with http:// or https://');

        $config = new Config();
        $config->checkConfig();
    }

    /**
     * Test with missing `BVRSS_YOUTUBE_API_KEY`
     */
    public function testWithMissingYouTubeApiKey(): void
    {
        putenv('BVRSS_SELF_URL_PATH=https://example.com/');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('YouTube API key must be set');

        $config = new Config();
        $config->checkConfig();
    }

    /**
     * Test with empty `BVRSS_YOUTUBE_API_KEY`
     */
    public function testWithEmptyYouTubeApiKey(): void
    {
        putenv('BVRSS_SELF_URL_PATH=https://example.com/');
        putenv('BVRSS_YOUTUBE_API_KEY=');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('YouTube API key must be set');

        $config = new Config();
        $config->checkConfig();
    }

    /**
     * Test empty `BVRSS_TIMEZONE`
     */
    public function testEmptyTimezone(): void
    {
        putenv('BVRSS_TIMEZONE=');

        $config = new Config();
        $config->checkOptional();

        $this->assertEquals('UTC', $config->getTimezone());
    }

    /**
     * Test `BVRSS_TIMEZONE` with invalid timezone
     */
    public function testInvalidTimezone(): void
    {
        putenv('BVRSS_TIMEZONE=Europe/Coventry');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid timezone given');

        $config = new Config();
        $config->checkOptional();
    }
}
