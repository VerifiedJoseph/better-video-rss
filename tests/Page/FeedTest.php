<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Config;
use App\Api;
use App\Http\Request;
use App\Page\Feed;

#[CoversClass(Feed::class)]
#[UsesClass(Config::class)]
#[UsesClass(Api::class)]
#[UsesClass(Request::class)]
#[UsesClass(App\Helper\Validate::class)]
class FeedTest extends AbstractTestCase
{
    private static Config $config;
    private static Request $request;
    private static Api $api;

    public static function setUpBeforeClass(): void
    {
        self::$config = new Config();
        self::$request = new Request(self::$config->getUserAgent());
        self::$api = new Api(self::$config, self::$request);
    }

    /**
     * Test checkInputs()
     */
    public function testCheckInputs(): void
    {
        $inputs = [
            'channel_id' => 'UCMufUaGlcuAvsSdzQV08BEA',
            'format' => 'html',
            'embed_videos' => 'true',
            'ignore_premieres' => 'false'
        ];

        $feed = new Feed($inputs, self::$config, self::$request, self::$api);
        $reflection = new ReflectionClass($feed);

        $this->assertEquals(
            $inputs['channel_id'],
            $reflection->getProperty('feedId')->getValue($feed)
        );

        $this->assertEquals(
            'channel',
            $reflection->getProperty('feedType')->getValue($feed)
        );

        $this->assertEquals(
            $inputs['format'],
            $reflection->getProperty('feedFormat')->getValue($feed)
        );

        $this->assertTrue($reflection->getProperty('embedVideos')->getValue($feed));
        $this->assertFalse($reflection->getProperty('ignorePremieres')->getValue($feed));
    }

    /**
     * Test checkInputs() with playlist Id
     */
    public function testCheckInputsWithPlaylistId(): void
    {
        $inputs = [
            'playlist_id' => 'PLMufUaGlcuAvsSdzQV08BEA'
        ];

        $feed = new Feed($inputs, self::$config, self::$request, self::$api);
        $reflection = new ReflectionClass($feed);

        $this->assertEquals(
            $inputs['playlist_id'],
            $reflection->getProperty('feedId')->getValue($feed)
        );

        $this->assertEquals(
            'playlist',
            $reflection->getProperty('feedType')->getValue($feed)
        );
    }

    /**
     * Test checkInputs() with empty channel id
     */
    public function testCheckInputsWithEmptyChannelId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid channel ID parameter given.');

        $inputs = [
            'channel_id' => ''
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }

    /**
     * Test checkInputs() with invalid channel id
     */
    public function testCheckInputsWithInvalidChannelId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid channel ID parameter given.');

        $inputs = [
            'channel_id' => 'NoAChannelId'
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }

    /**
     * Test checkInputs() with empty playlist id
     */
    public function testCheckInputsWithEmptyPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid playlist ID parameter given.');

        $inputs = [
            'playlist_id' => ''
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }

    /**
     * Test checkInputs() with invalid playlist ID
     */
    public function testCheckInputsWithInvalidPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid playlist ID parameter given.');

        $inputs = [
            'playlist_id' => 'NoAPlaylistId'
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }

    /**
     * Test checkInputs() with no channel or playlist ID given
     */
    public function testCheckInputsWithNoChannelOrPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No channel or playlist ID parameter given.');

        new Feed([], self::$config, self::$request, self::$api);
    }

    /**
     * Test checkInputs() with unsupported feed format
     */
    public function testCheckInputsWithUnsupportedFormat(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid format parameter given.');

        $inputs = [
            'format' => 'yaml'
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }
}
