<?php

use PHPUnit\Framework\TestCase;
use App\Helper\Url;

class UrlTest extends TestCase
{
    private string $selfUrl = 'https://example.com/';

    /** @var array<int, array<string, mixed>> $feedUrls */
    private array $feedUrls = [
        [
            'url' => 'https://example.com/feed.php?channel_id=UCBa659QWEk1AI4Tg--mrJ2A&format=html',
            'type' => 'channel',
            'id' => 'UCBa659QWEk1AI4Tg--mrJ2A',
            'format' => 'html',
            'embed' => false
        ],
        [
            'url' => 'https://example.com/feed.php?channel_id=UCBa659QWEk1AI4Tg--mrJ2A&format=rss&embed_videos=true',
            'type' => 'channel',
            'id' => 'UCBa659QWEk1AI4Tg--mrJ2A',
            'format' => 'rss',
            'embed' => true
        ],
        [
            'url' => 'https://example.com/feed.php?playlist_id=PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC&format=json',
            'type' => 'playlist',
            'id' => 'PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC',
            'format' => 'json',
            'embed' => false
        ]
    ];

    /** @var array<int, array<string, string>> $thumbnailTypes */
    private array $thumbnailTypes = [
        [
            'url' => 'https://i.ytimg.com/vi/jNQXAC9IVRw/hqdefault.jpg',
            'type' => 'hqdefault',
        ],
        [
            'url' => 'https://i.ytimg.com/vi/jNQXAC9IVRw/sddefault.jpg',
            'type' => 'sddefault',
        ],
        [
            'url' => 'https://i.ytimg.com/vi/jNQXAC9IVRw/maxresdefault.jpg',
            'type' => 'maxresdefault',
        ],
        [
            'url' => 'https://i.ytimg.com/vi/jNQXAC9IVRw/hqdefault.jpg',
            'type' => 'unsupported-use-fallback',
        ]
    ];

    /**
     * Test `GetFeed()`
     */
    public function testGetFeed(): void
    {
        foreach ($this->feedUrls as $item) {
            $this->assertEquals($item['url'], Url::getFeed(
                $this->selfUrl,
                $item['type'],
                $item['id'],
                $item['format'],
                $item['embed']
            ));
        }
    }

    /**
     * Test `getImageProxy()`
     */
    public function testGetImageProxy(): void
    {
        $url = 'https://example.com/proxy.php?video_id=jNQXAC9IVRw&channel_id=UC4QobU6STFB0P71PMvOGN5A';

        $this->assertEquals($url, Url::getImageProxy(
            $this->selfUrl,
            'jNQXAC9IVRw',
            'channel',
            'UC4QobU6STFB0P71PMvOGN5A',
        ));
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
        foreach ($this->thumbnailTypes as $item) {
            $this->assertEquals($item['url'], Url::getThumbnail('jNQXAC9IVRw', $item['type']));
        }
    }

    /**
     * Test `getApi()`
     */
    /*public function tesGetApi(): void
    {
    }*/
}
