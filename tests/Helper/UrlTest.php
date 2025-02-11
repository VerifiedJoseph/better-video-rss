<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Helper\Url;

#[CoversClass(Url::class)]
class UrlTest extends TestCase
{
    private string $selfUrl = 'https://example.com/';

    private static stdClass $urls;

    public static function setUpBeforeClass(): void
    {
        self::$urls = json_decode((string) file_get_contents('tests/files/helper-url-samples.json'));
    }

    /**
     * Test `GetFeed()`
     */
    public function testGetFeed(): void
    {
        foreach (self::$urls->feeds as $item) {
            $this->assertEquals($item->url, Url::getFeed(
                $this->selfUrl,
                $item->type,
                $item->id,
                $item->format,
                $item->embed,
                $item->ignore_premieres
            ));
        }
    }

    /**
     * Test `getRssFeed()`
     */
    public function testGetRssFeed(): void
    {
        $channelFeedUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id=UC4QobU6STFB0P71PMvOGN5A';
        $playlistFeedUrl = 'https://www.youtube.com/feeds/videos.xml?playlist_id=PLuhl9TnQPDCnWIhy_KSbtFwXVQnNvgfSh';

        $this->assertEquals($channelFeedUrl, Url::getRssFeed('channel', 'UC4QobU6STFB0P71PMvOGN5A'));
        $this->assertEquals($playlistFeedUrl, Url::getRssFeed('playlist', 'PLuhl9TnQPDCnWIhy_KSbtFwXVQnNvgfSh'));
    }

    /**
     * Test `getChannel()`
     */
    public function testGetChannel(): void
    {
        $this->assertEquals(
            'https://www.youtube.com/channel/UC4QobU6STFB0P71PMvOGN5A',
            Url::getChannel('UC4QobU6STFB0P71PMvOGN5A')
        );
    }

    /**
     * Test `getPlaylist()`
     */
    public function testGetPlaylist(): void
    {
        $this->assertEquals(
            'https://www.youtube.com/playlist?list=PLuhl9TnQPDCnWIhy_KSbtFwXVQnNvgfSh',
            Url::getPlaylist('PLuhl9TnQPDCnWIhy_KSbtFwXVQnNvgfSh')
        );
    }

    /**
     * Test `getVideo()`
     */
    public function testGetVideo(): void
    {
        $this->assertEquals(
            'https://www.youtube.com/watch?v=jNQXAC9IVRw',
            Url::getVideo('jNQXAC9IVRw')
        );
    }

    /**
     * Test `getEmbed()`
     */
    public function testGetEmbed(): void
    {
        $this->assertEquals(
            'https://www.youtube-nocookie.com/embed/jNQXAC9IVRw',
            Url::getEmbed('jNQXAC9IVRw')
        );
    }

    /**
     * Test `getThumbnail()`
     */
    public function testGetThumbnail(): void
    {
        foreach (self::$urls->thumbnails as $item) {
            $this->assertEquals($item->url, Url::getThumbnail('jNQXAC9IVRw', $item->type));
        }
    }

    /**
     * Test `getApi()`
     */
    public function testGetApi(): void
    {
        foreach (self::$urls->apis as $item) {
            $this->assertEquals($item->url, Url::getApi($item->type, $item->value, 'ApiKeyHere'));
        }
    }
}
