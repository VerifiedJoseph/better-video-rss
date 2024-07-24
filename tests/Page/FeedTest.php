<?php

use PHPUnit\Framework\TestCase;
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
class FeedTest extends TestCase
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
     * Test class with empty channel id
     */
    public function testClassWithEmptyChannelId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid channel ID parameter given.');

        $inputs = [
            'channel_id' => ''
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }

    /**
     * Test class with invalid channel id
     */
    public function testClassWithInvalidChannelId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid channel ID parameter given.');

        $inputs = [
            'channel_id' => 'NoAChannelId'
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }

    /**
     * Test class with empty playlist id
     */
    public function testClassWithEmptyPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid playlist ID parameter given.');

        $inputs = [
            'playlist_id' => ''
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }

    /**
     * Test class with invalid playlist ID
     */
    public function testClassWithInvalidPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid playlist ID parameter given.');

        $inputs = [
            'playlist_id' => 'NoAPlaylistId'
        ];

        new Feed($inputs, self::$config, self::$request, self::$api);
    }

    /**
     * Test class with no channel or playlist ID given
     */
    public function testClassWithNoChannelOrPlaylistId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No channel or playlist ID parameter given.');

        new Feed([], self::$config, self::$request, self::$api);
    }
}
