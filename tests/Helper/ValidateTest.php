<?php

use PHPUnit\Framework\TestCase;
use App\Helper\Validate;

class ValidateTest extends TestCase
{
    /**
     * Test `timezone()`
     */
    public function testTimezone(): void
    {
        $this->assertTrue(Validate::timezone('Europe/London'));
        $this->assertFalse(Validate::timezone('Europe/Narnia'));
    }

    /**
     * Test `feedFormat()`
     */
    public function testFeedFormat(): void
    {
        $this->assertTrue(Validate::feedFormat('html', ['html', 'rss']));
        $this->assertFalse(Validate::feedFormat('yaml', ['html', 'rss']));
    }

    /**
     * Test `channelId()`
     */
    public function testChannelId(): void
    {
        $this->assertTrue(Validate::channelId('UCBa659QWEk1AI4Tg--mrJ2A'));
        $this->assertFalse(Validate::channelId('Ba659QWEk1AI4Tg--mrJ2A'));
    }

    /**
     * Test `playlistId()`
     */
    public function testPlaylistId(): void
    {
        $this->assertTrue(Validate::playlistId('PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC'));
        $this->assertTrue(Validate::playlistId('UUBa659QWEk1AI4Tg--mrJ2A'));
        $this->assertFalse(Validate::playlistId('Ba659QWEk1AI4Tg--mrJ2A'));
    }

    /**
     * Test `videoId()`
     */
    public function testVideoId(): void
    {
        $this->assertTrue(Validate::videoId('jNQXAC9IVRw'));
        $this->assertFalse(Validate::videoId('HelloWorld?'));
    }

    /**
     * Test `youTubeUrl()`
     */
    public function testYouTubeUrl(): void
    {
        $this->assertTrue(Validate::youTubeUrl('http://youtube.com/'));
        $this->assertTrue(Validate::youTubeUrl('http://www.youtube.com/'));
        $this->assertTrue(Validate::youTubeUrl('https://youtube.com/'));
        $this->assertTrue(Validate::youTubeUrl('https://www.youtube.com/'));
        $this->assertFalse(Validate::youTubeUrl('https://example.com/'));
    }

    /**
     * Test `selfUrlHttp()`
     */
    public function testSelfUrlHttp(): void
    {
        $this->assertTrue(Validate::selfUrlHttp('http://example.com/'));
        $this->assertTrue(Validate::selfUrlHttp('https://example.com/'));
        $this->assertFalse(Validate::selfUrlHttp('example.com/'));
    }

    /**
     * Test `selfUrlSlash()`
     */
    public function testSelfUrlSlash(): void
    {
        $this->assertTrue(Validate::selfUrlSlash('https://example.com/'));
        $this->assertFalse(Validate::selfUrlSlash('https://example.com'));
    }

    /**
     * Test `absolutePath()`
     */
    public function testAbsolutePath(): void
    {
        $this->assertTrue(Validate::absolutePath('/app/cache'));
        $this->assertTrue(Validate::absolutePath('Z:\\app\cache'));
        $this->assertFalse(Validate::absolutePath('app\cache'));
    }
}
