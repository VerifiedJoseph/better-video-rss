<?php

use PHPUnit\Framework\TestCase;
use App\Config;
use App\Api;
use App\Http\Request;
use App\Page\Index;

class IndexTest extends TestCase
{
    private static Config $config;
    private static Api $api;

    public static function setUpBeforeClass(): void
    {
        self::$config = new Config();
        self::$api = new Api(self::$config, new Request(self::$config->getUserAgent()));
    }

    /**
     * Test class with empty query
     */
    public function testClassWithEmptyQuery(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Query parameter not given.');

        $inputs = [
            'query' => ''
        ];

        new Index($inputs, self::$config, self::$api);
    }

    /**
     * Test class with empty type
     */
    public function testClassWithEmptyType(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Type parameter not given.');

        $inputs = [
            'query' => 'Hello World',
            'type' => ''
        ];

        new Index($inputs, self::$config, self::$api);
    }

    /**
     * Test class with unsupported type
     */
    public function testClassWithUnsupportedType(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown type parameter given.');

        $inputs = [
            'query' => 'Hello World',
            'type' => 'fake-type-here'
        ];

        new Index($inputs, self::$config, self::$api);
    }

    /**
     * test `display()` with channel query
     */
    public function testDisplayWithChannelQuery(): void
    {
        $this->expectOutputString(
            (string) file_get_contents('tests/files/Pages/expected-index-channel-feed-url.html')
        );

        /** @var PHPUnit\Framework\MockObject\Stub&Api */
        $api = $this->createStub(Api::class);
        $api->method('searchChannels')->willReturn((object) [
            'items' => [
                (object) [
                    'id' => (object) ['channelId' => 'UCMufUaGlcuAvsSdzQV08BEA'],
                    'snippet' => (object) ['title' => 'Example channel']
                ]
            ]
        ]);

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = $this->createStub(Config::class);
        $config->method('getVersion')->willReturn('0.0.0');
        $config->method('getDefaultFeedFormat')->willReturn('html');
        $config->method('getFeedFormats')->willReturn(['rss', 'html', 'json']);

        $inputs = [
            'query' => 'Hello World',
            'type' => 'channel'
        ];

        $index = new Index($inputs, $config, $api);
        $index->display();
    }

    /**
     * test `display()` with playlist query
     */
    public function testDisplayWithPlaylistQuery(): void
    {
        $this->expectOutputString(
            (string) file_get_contents('tests/files/Pages/expected-index-playlist-feed-url.html')
        );

        /** @var PHPUnit\Framework\MockObject\Stub&Api */
        $api = $this->createStub(Api::class);
        $api->method('searchPlaylists')->willReturn((object) [
            'items' => [
                (object) [
                    'id' => (object) ['playlistId' => 'PLuQSJ2zY5-wSWuKSVrokXq7mDCRqGPsFW'],
                    'snippet' => (object) ['title' => 'Example playlist']
                ]
            ]
        ]);

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = $this->createStub(Config::class);
        $config->method('getVersion')->willReturn('0.0.0');
        $config->method('getDefaultFeedFormat')->willReturn('html');
        $config->method('getFeedFormats')->willReturn(['rss', 'html', 'json']);

        $inputs = [
            'query' => 'Hello World',
            'type' => 'playlist'
        ];

        $index = new Index($inputs, $config, $api);
        $index->display();
    }

    /**
     * test `display()` with URL query
     */
    public function testDisplayWithUrlQuery(): void
    {
        $this->expectOutputString(
            (string) file_get_contents('tests/files/Pages/expected-index-from-url-feed-url.html')
        );

        /** @var PHPUnit\Framework\MockObject\Stub&Api */
        $api = $this->createStub(Api::class);

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = $this->createStub(Config::class);
        $config->method('getVersion')->willReturn('0.0.0');
        $config->method('getDefaultFeedFormat')->willReturn('html');
        $config->method('getFeedFormats')->willReturn(['rss', 'html', 'json']);

        $inputs = [
            'query' => 'https://www.youtube.com/channel/UCMufUaGlcuAvsSdzQV08BEA',
            'type' => 'url'
        ];

        $index = new Index($inputs, $config, $api);
        $index->display();
    }
}
