<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Config;
use App\Proxy;
use App\Http\Request;
use App\Http\Response;

#[CoversClass(Proxy::class)]
#[UsesClass(Config::class)]
#[UsesClass(Request::class)]
#[UsesClass(Response::class)]
#[UsesClass(App\Cache::class)]
#[UsesClass(App\Helper\File::class)]
#[UsesClass(App\Helper\Json::class)]
#[UsesClass(App\Helper\Validate::class)]
class ProxyTest extends AbstractTestCase
{
    private static Config $config;
    private static Request $request;

    private string $videoId = 'Owd0fCoJhiv';
    private static string $channelId = 'UCMufUaGlcuAvsSdzQV08BEA';
    private string $playlistId = 'PLMufUaGlcuAvsSdzQV08BEA';

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
        $config->method('getCacheDirectory')->willReturn(sys_get_temp_dir());
        $config->method('getCacheFormatVersion')->willReturn(1);
        self::$config = $config;

        self::$request = new Request('test');
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$cacheFilepath);
    }

    /**
     * Test checkInputs() with playlist ID
     */
    public function testCheckInputs(): void
    {
        $inputs = [
            'video_id' => $this->videoId,
            'channel_id' => self::$channelId
        ];

        $proxy = new Proxy($inputs, self::$config, self::$request);
        $reflection = new ReflectionClass($proxy);

        $this->assertEquals(
            $inputs['video_id'],
            $reflection->getProperty('videoId')->getValue($proxy)
        );

        $this->assertEquals(
            $inputs['channel_id'],
            $reflection->getProperty('feedId')->getValue($proxy)
        );
    }

    /**
     * Test checkInputs() with playlist ID
     */
    public function testCheckInputsWithPlaylistId(): void
    {
        $inputs = [
            'video_id' => $this->videoId,
            'playlist_id' => $this->playlistId
        ];

        $proxy = new Proxy($inputs, self::$config, self::$request);
        $reflection = new ReflectionClass($proxy);

        $this->assertEquals(
            $inputs['playlist_id'],
            $reflection->getProperty('feedId')->getValue($proxy)
        );
    }

    /**
     * Test checkInputs() with Image proxy option disabled
     */
    public function testCheckInputsWithImageProxyDisabled(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Image proxy is disabled.');

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = $this->createStub(Config::class);
        $config->method('getImageProxyStatus')->willReturn(false);

        new Proxy([], $config, self::$request);
    }

    /**
     * Test checkInputs() with no video ID
     */
    public function testCheckInputsWithNoVideoId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No video ID parameter given.');

        new Proxy([], self::$config, self::$request);
    }

    /**
     * Test checkInputs() with empty video ID
     */
    public function testCheckInputsWithEmptyVideoId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No video ID parameter given.');

        $inputs = [
            'video_id' => ''
        ];

        new Proxy($inputs, self::$config, self::$request);
    }

    /**
     * Test checkInputs() with invalid video ID
     */
    public function testCheckInputsWithInvalidVideoId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid video ID parameter given.');

        $inputs = [
            'video_id' => 'hello&world',
        ];

        new Proxy($inputs, self::$config, self::$request);
    }

    /**
     * Test checkInputs() with empty channel ID
     */
    public function testCheckInputsWithEmptyChannelId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No channel ID parameter given.');

        $inputs = [
            'video_id' => $this->videoId,
            'channel_id' => ''
        ];

        new Proxy($inputs, self::$config, self::$request);
    }

    /**
     * Test checkInputs() with invalid channel ID
     */
    public function testCheckInputsWithInvalidChannelId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid channel ID parameter given.');

        $inputs = [
            'video_id' => $this->videoId,
            'channel_id' => 'NoAChannelId'
        ];

        new Proxy($inputs, self::$config, self::$request);
    }

    /**
     * Test checkInputs() class with empty playlist ID
     */
    public function testCheckInputsWithEmptyPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No playlist ID parameter given.');

        $inputs = [
            'video_id' => $this->videoId,
            'playlist_id' => ''
        ];

        new Proxy($inputs, self::$config, self::$request);
    }

    /**
     * Test checkInputs() with invalid playlist ID given
     */
    public function testCheckInputsWithInvalidPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid playlist ID parameter given.');

        $inputs = [
            'video_id' => $this->videoId,
            'playlist_id' => 'NoAPlaylistId'
        ];

        new Proxy($inputs, self::$config, self::$request);
    }

    /**
     * Test checkInputs() with no channel or playlist ID given
     */
    public function testCheckInputsWithNoFeedId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No feed ID (channel or playlist) parameter given.');

        $inputs = [
            'video_id' => $this->videoId
        ];

        new Proxy($inputs, self::$config, self::$request);
    }

    /**
     * Test `getImage`
     */
    public function testGetImage(): void
    {
        $responseBody = 'test';

        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response($responseBody, 200));

        $inputs = [
            'video_id' => $this->videoId,
            'channel_id' => self::$channelId
        ];

        $proxy = new Proxy($inputs, self::$config, $request);
        $proxy->getImage();

        $reflection = new \ReflectionClass($proxy);
        $this->assertEquals($responseBody, $reflection->getProperty('image')->getValue($proxy));
    }

    /**
     * Test `getImage` with feed ID not in cache
     */
    public function testGetImageWithFeedIdNotInCache(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Feed ID not in cache');

        $inputs = [
            'video_id' => $this->videoId,
            'channel_id' => 'UCChannelIdNotInCache'
        ];

        $proxy = new Proxy($inputs, self::$config, self::$request);
        $proxy->getImage();
    }

    /**
     * Test `getImage` with video ID not in cache
     */
    public function testGetImageWithVideoIdNotInCache(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Video ID not in cache');

        $inputs = [
            'video_id' => 'dQw4w9WgXcQ',
            'channel_id' => self::$channelId
        ];

        $proxy = new Proxy($inputs, self::$config, self::$request);
        $proxy->getImage();
    }
}
