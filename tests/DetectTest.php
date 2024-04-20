<?php

use PHPUnit\Framework\TestCase;
use App\Detect;

class DetectTest extends TestCase
{
    private static stdClass $urls;

    public static function setUpBeforeClass(): void
    {
        self::$urls = json_decode((string) file_get_contents('tests/files/detect-from-url-samples.json'));
    }

    /**
     * Test `fromUrl()` with YouTube channel URL
     */
    public function testFromUrlWithChannelUrl(): void
    {
        $detect = new Detect();

        $this->assertTrue($detect->fromUrl(self::$urls->channels[0]->url));
        $this->assertEquals('channel', $detect->getType());
        $this->assertEquals('UCBa659QWEk1AI4Tg--mrJ2A', $detect->getValue());
    }

    /**
     * Test `fromUrl()` with YouTube Playlist URLs
     */
    public function testFromUrlWithPlaylistUrls(): void
    {
        foreach (self::$urls->playlists as $item) {
            $detect = new Detect();

            $this->assertTrue($detect->fromUrl($item->url));
            $this->assertEquals('playlist', $detect->getType());
            $this->assertEquals($item->value, $detect->getValue());
        }
    }

    /**
     * Test `fromUrl()` with YouTube username URLs
     */
    public function testFromUrlWithUsernameUrls(): void
    {
        foreach (self::$urls->usernames as $item) {
            $detect = new Detect();

            $this->assertTrue($detect->fromUrl($item->url));
            $this->assertEquals('channel', $detect->getType());
            $this->assertEquals($item->value, $detect->getValue());
        }
    }

    /**
     * Test `fromUrl()` with YouTube RSS feed URLs
     */
    public function testFromUrlWithRssFeedUrl(): void
    {
        foreach (self::$urls->feeds as $item) {
            $detect = new Detect();

            $this->assertTrue($detect->fromUrl($item->url));
            $this->assertEquals($item->type, $detect->getType());
            $this->assertEquals($item->value, $detect->getValue());
        }
    }

    /**
     * Test `fromUrl()` with an unsupported URL
     */
    public function testFromUrlWithUnsupportedUrl(): void
    {
        $detect = new Detect();
        $this->assertFalse($detect->fromUrl('https://example.com'));
    }
}
