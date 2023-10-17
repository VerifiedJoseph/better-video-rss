<?php

use PHPUnit\Framework\TestCase;
use App\Detect;

class DetectTest extends TestCase
{
    /** @var array<int, array<string, string>> $playlistUrls YouTube playlist URLs */
    private array $playlistUrls = [
        [
            'url' => 'https://www.youtube.com/playlist?list=PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC',
            'value' => 'PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC'
        ],
        [
            'url' => 'https://www.youtube.com/playlist?list=UUBa659QWEk1AI4Tg--mrJ2A',
            'value' => 'UUBa659QWEk1AI4Tg--mrJ2A'
        ],
        [
            'url' => 'https://www.youtube.com/watch?v=TfVYxnhuEdU&list=UUBa659QWEk1AI4Tg--mrJ2A',
            'value' => 'UUBa659QWEk1AI4Tg--mrJ2A'
        ],
        [
            'url' => 'https://www.youtube.com/watch?v=ZNVuIU6UUiM&list=PL96C35uN7xGI9HGKHsArwxiOejecVyNem&index=1',
            'value' => 'PL96C35uN7xGI9HGKHsArwxiOejecVyNem'
        ],
    ];

    /** @var array<int, array<string, string>> $usernameUrls YouTube username URLs */
    private array $usernameUrls = [
        [
            'url' => 'https://www.youtube.com/c/TomScottGo',
            'value' => 'TomScottGo'
        ],
        [
            'url' => 'https://www.youtube.com/user/enyay',
            'value' => 'enyay'
        ],
        [
            'url' => 'https://www.youtube.com/@TomScottGo',
            'value' => 'TomScottGo'
        ],
    ];

    /** @var array<int, array<string, string>> $rssFeedUrls YouTube RSS feed URLs */
    private array $rssFeedUrls = [
        [
            'url' => 'https://www.youtube.com/feeds/videos.xml?user=enyay',
            'type' => 'channel',
            'value' => 'enyay'
        ],
        [
            'url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCBa659QWEk1AI4Tg--mrJ2A',
            'type' => 'channel',
            'value' => 'UCBa659QWEk1AI4Tg--mrJ2A'
        ],
        [
            'url' => 'https://www.youtube.com/feeds/videos.xml?playlist_id=PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC',
            'type' => 'playlist',
            'value' => 'PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC'
        ],
    ];

    /**
     * Test `fromUrl()` with a YouTube channel URL
     */
    public function testFromUrlWithChannelUrl(): void
    {
        $detect = new Detect();
        $detect->fromUrl('https://www.youtube.com/channel/UCBa659QWEk1AI4Tg--mrJ2A');

        $this->assertEquals('channel', $detect->getType());
        $this->assertEquals('UCBa659QWEk1AI4Tg--mrJ2A', $detect->getValue());
    }

    /**
     * Test `fromUrl()` with a YouTube Playlist URLs
     */
    public function testFromUrlWithPlaylistUrls(): void
    {
        foreach ($this->playlistUrls as $playlist) {
            $detect = new Detect();
            $detect->fromUrl($playlist['url']);

            $this->assertEquals('playlist', $detect->getType());
            $this->assertEquals($playlist['value'], $detect->getValue());
        }
    }

    /**
     * Test `fromUrl()` with a YouTube username URLs
     */
    public function testFromUrlWithUsernameUrls(): void
    {
        foreach ($this->usernameUrls as $username) {
            $detect = new Detect();
            $detect->fromUrl($username['url']);

            $this->assertEquals('channel', $detect->getType());
            $this->assertEquals($username['value'], $detect->getValue());
        }
    }

    /**
     * Test `fromUrl()` with a YouTube RSS feed URLs
     */
    public function testFromUrlWithRssFeedUrl(): void
    {
        foreach ($this->rssFeedUrls as $feed) {
            $detect = new Detect();
            $detect->fromUrl($feed['url']);

            $this->assertEquals($feed['type'], $detect->getType());
            $this->assertEquals($feed['value'], $detect->getValue());
        }
    }
}
