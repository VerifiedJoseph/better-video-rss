<?php

use PHPUnit\Framework\TestCase;
use App\Config;
use App\Proxy;

class ProxyTest extends TestCase
{
    private static Config $config;

    private string $videoId = 'CkZyZFa5qO0';
    private static string $channelId = 'UCBa659QWEk1AI4Tg--mrJ2A';

    private static string $cacheFilepath = '';

    public static function setUpBeforeClass(): void
    {
        self::$cacheFilepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . hash('sha256', self::$channelId) . '.cache';

        copy('tests/files/channel-cache-data.json', self::$cacheFilepath);

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = self::createStub(Config::class);
        $config->method('getImageProxyStatus')->willReturn(true);
        $config->method('getCacheDisableStatus')->willReturn(false);
        $config->method('getSelfUrl')->willReturn('https://example.com/');
        $config->method('getTimezone')->willReturn('Europe/London');
        $config->method('getDateFormat')->willReturn('F j, Y');
        $config->method('getTimeFormat')->willReturn('H:i');
        $config->method('getCacheDirPath')->willReturn(sys_get_temp_dir());
        $config->method('getCacheFileExtension')->willReturn('cache');
        self::$config = $config;
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$cacheFilepath);
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    /**
     * Test Proxy class with ImageProxy disabled
     *
     * An exception should be thrown.
     */
    public function testExceptionImageProxyDisabled(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Image proxy is disabled.');

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = $this->createStub(Config::class);
        $config->method('getImageProxyStatus')->willReturn(false);

        new Proxy([], $config);
    }

    /**
     * Test Proxy class with no video ID given with `$_GET`
     *
     * An exception should be thrown.
     */
    public function testExceptionImageNoVideoId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No video ID parameter given.');

        new Proxy([], self::$config);
    }

    /**
     * Test Proxy class with empty video ID given with `$_GET`
     *
     * An exception should be thrown.
     */
    public function testExceptionImagEmptyVideoId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No video ID parameter given.');

        $inputs = [
            'video_id' => ''
        ];

        new Proxy($inputs, self::$config);
    }

    /**
     * Test Proxy class with empty channel ID given with `$_GET`
     *
     * An exception should be thrown.
     */
    public function testExceptionImagEmptyChannelId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No channel ID parameter given.');

        $inputs = [
            'video_id' => $this->videoId,
            'channel_id' => ''
        ];

        new Proxy($inputs, self::$config);
    }

    /**
     * Test Proxy class with empty channel ID given with `$_GET`
     *
     * An exception should be thrown.
     */
    public function testExceptionImagInvalidChannelId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid channel ID parameter given.');

        $inputs = [
            'video_id' => $this->videoId,
            'channel_id' => 'NoAChannelId'
        ];

        new Proxy($inputs, self::$config);
    }

    /**
     * Test Proxy class with empty playlist ID given with `$_GET`
     *
     * An exception should be thrown.
     */
    public function testExceptionImagEmptyPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No playlist ID parameter given.');

        $inputs = [
            'video_id' => $this->videoId,
            'playlist_id' => ''
        ];

        new Proxy($inputs, self::$config);
    }

    /**
     * Test Proxy class with empty playlist ID given with `$_GET`
     *
     * An exception should be thrown.
     */
    public function testExceptionImagInvalidPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid playlist ID parameter given.');

        $inputs = [
            'video_id' => $this->videoId,
            'playlist_id' => 'NoAPlaylistId'
        ];

        new Proxy($inputs, self::$config);
    }

    /**
     * Test Proxy class with no channel or playlist ID given with `$_GET`
     *
     * An exception should be thrown.
     */
    public function testExceptionImagNoFeedId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No feed ID (channel or playlist) parameter given.');

        $inputs = [
            'video_id' => $this->videoId
        ];

        new Proxy($inputs, self::$config);
    }

    /**
     * Test `getImage` with feed ID not in cache
     *
     * An exception should be thrown.
     */
    public function testExceptionFeedIdNoInCache(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Feed ID not in cache');

        $inputs = [
            'video_id' => $this->videoId,
            'channel_id' => 'UCChannelIdNotInCache'
        ];

        $proxy = new Proxy($inputs, self::$config);
        $proxy->getImage();
    }

    /**
     * Test `getImage` with video ID not in cache
     *
     * An exception should be thrown.
     */
    public function testExceptionVideoIdNoInCache(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Video ID not in cache');

        $inputs = [
            'video_id' => 'dQw4w9WgXcQ',
            'channel_id' => self::$channelId
        ];

        $proxy = new Proxy($inputs, self::$config);
        $proxy->getImage();
    }
}
