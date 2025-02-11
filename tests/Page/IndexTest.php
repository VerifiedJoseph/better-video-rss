<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Config;
use App\Api;
use App\Http\Request;
use App\Page\Index;

#[CoversClass(Index::class)]
#[UsesClass(Config::class)]
#[UsesClass(App\Find::class)]
#[UsesClass(App\Detect::class)]
#[UsesClass(App\Template::class)]
#[UsesClass(App\Helper\File::class)]
#[UsesClass(App\Helper\Format::class)]
#[UsesClass(App\Helper\Output::class)]
#[UsesClass(App\Helper\Url::class)]
#[UsesClass(App\Helper\Validate::class)]
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
     * Test checkInputs()
     */
    public function testCheckInputs(): void
    {
        $inputs = [
            'query' => 'UCBa659QWEk1AI4Tg--mrJ2A',
            'type' => 'channel',
            'format' => 'html',
            'embed_videos' => 'true',
            'ignore_premieres' => 'false'
        ];

        $index = new Index($inputs, self::$config, self::$api);
        $reflection = new ReflectionClass($index);

        $this->assertEquals(
            $inputs['format'],
            $reflection->getProperty('feedFormat')->getValue($index)
        );

        $this->assertTrue($reflection->getProperty('embedVideos')->getValue($index));
        $this->assertFalse($reflection->getProperty('ignorePremieres')->getValue($index));
    }

    /**
     * Test checkInputs() with empty query
     */
    public function testCheckInputsWithEmptyQuery(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Query parameter not given.');

        $inputs = [
            'query' => ''
        ];

        new Index($inputs, self::$config, self::$api);
    }

    /**
     * Test checkInputs() with empty type
     */
    public function testCheckInputsWithEmptyType(): void
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
     * Test checkInputs() with unsupported type
     */
    public function testCheckInputsWithUnsupportedType(): void
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
     * Test generate() with invalid YouTube URL
     */
    public function testGenerateWithInvalidYouTubeUrl(): void
    {
        $inputs = [
            'query' => 'https://example.com',
            'type' => 'url'
        ];

        $index = new Index($inputs, self::$config, self::$api);
        $reflection = new \ReflectionClass($index);

        $this->assertTrue($reflection->getProperty('error')->getValue($index));
        $this->assertEquals(
            'URL is not a valid YouTube URL.',
            $reflection->getProperty('errorMessage')->getValue($index)
        );
    }

    /**
     * Test generate() with unsupported YouTube URL
     */
    public function testGenerateWithUnsupportedYouTubeUrl(): void
    {
        $inputs = [
            'query' => 'https://youtube.com/channel/',
            'type' => 'url'
        ];

        $index = new Index($inputs, self::$config, self::$api);
        $reflection = new \ReflectionClass($index);

        $this->assertTrue($reflection->getProperty('error')->getValue($index));
        $this->assertEquals(
            'Unsupported YouTube URL.',
            $reflection->getProperty('errorMessage')->getValue($index)
        );
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

    /**
     * test `display()` with error message
     */
    public function testDisplayWithErrorMessage(): void
    {
        $this->expectOutputString(
            (string) file_get_contents('tests/files/Pages/expected-index-with-error-message.html')
        );

        /** @var PHPUnit\Framework\MockObject\Stub&Api */
        $api = $this->createStub(Api::class);

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = $this->createStub(Config::class);
        $config->method('getVersion')->willReturn('0.0.0');
        $config->method('getDefaultFeedFormat')->willReturn('html');
        $config->method('getFeedFormats')->willReturn(['rss', 'html', 'json']);

        $inputs = [
            'query' => 'https://www.example.com/channel/UCMufUaGlcuAvsSdzQV08BEA',
            'type' => 'url'
        ];

        $index = new Index($inputs, $config, $api);
        $index->display();
    }
}
